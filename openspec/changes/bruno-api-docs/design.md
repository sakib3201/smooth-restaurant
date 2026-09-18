## Context

Bruno v4 is a Git-native API client: collections are plain-text files in the repo, runnable headlessly via `@usebruno/cli` (npm `latest` = 4.1.0 at plan time — pin exact at implementation), with JUnit/JSON/HTML reporters and an official GitHub Action (`usebruno/bruno-cli-action@v1`). Research source: https://docs.usebruno.com (v4 docs, changelog v4.0.0/v4.1.0, Sep 2026); CLI registry metadata verified 2026-09-18.

Current state: no API client artifacts in repo. SMO-121 (`Todo`) will ship the first endpoints; SMO-122 (`Todo`) owns demo fixtures. CI runs on `ubuntu-24.04` (Docker available → wp-env boots in CI; locally Docker is absent). Hard constraints: zero-secrets lock in `ci-gates` spec (KEPT — no `secrets.*` in any workflow), GitHub billing lock (no Actions runs provable right now), LF endings, PR triggers only on `development`/`release/*`.

## Goals / Non-Goals

**Goals:**
- Versioned, reviewable API documentation that doubles as executable contract tests.
- Conventions carved in stone before SMO-121 authors the first requests (naming, docs template, assertions, tags, envs, secrets).
- CI runs the public contract advisory-first, promotable to required by an event-based rule without restructuring.
- Locked `sm_docs §13.6` stays truthful (amended where this design overrides it); `ci-gates` no-secrets lock untouched.

**Non-Goals:**
- Generated HTML docs site publishing (feeds the later docs-site track; not deployed here).
- Mock servers, Postman collection generation, Next.js starter (downstream of `openapi.json`, later changes).
- Webhook event catalog (no webhooks exist yet).
- The WP-native OAS emitter (filed as tracked follow-up; interim seed + retirement condition here).
- Replacing PHPUnit/Playwright money-path suites — Bruno covers API contract + smoke, not payment flows.

## Decisions

### 1. OpenCollection YAML over `.bru` (amends locked §13.6)
Bruno recommends OpenCollection YAML for new collections; v4.1 ships one-click `.bru`→YAML migration; CLI OpenAPI folder import defaults to `opencollection`. YAML suits existing lint tooling and agents. `.bru` (Bru lang) remains fully supported, so risk is low. The §13.6 line locking `/api-docs/*.bru` is amended to OpenCollection YAML as the authoring default. Alternative (stay on `.bru` per letter of the lock) rejected: fights the tool's direction for zero benefit.

### 2. Layout: `api-docs/smooth-v1/` collection root, lowercase envs
```
api-docs/
  openapi.json                # interim hand-maintained seed (menus paths only)
  smooth-v1/
    opencollection.yml        # collection config; default env set via Collection > Presets UI
    .env.sample               # secret names only (SMOOTH_BASIC_AUTH, base-URL override)
    environments/
      local.yml               # wp-env http://localhost:8889, non-secret vars + {{process.env.*}} refs
      ci.yml                  # runner wp-env URL, same shape, NO secret refs
    menus/
      01-list-menus.yml
      02-get-menu.yml         # authored with SMO-121, not before
    orders/ reservations/ tables/ qr/ system/   # skeleton folders w/ folder docs now
    docs/request-template.md  # Docs-tab template (reference copy)
```
`.env` lives at `api-docs/smooth-v1/.env` (Bruno loads it from the collection root only), gitignored. Env names are lowercase everywhere (`local`, `ci`) — they are case-sensitive file lookups on `ubuntu-24.04`. The default environment is set through the Collection > Presets UI, never hand-edited (exact config key path is version-sensitive). `.env.sample` is a convention for sharing structure; only `.env` loads.

### 3. Schemas authoritative; deterministic extract-script gate (interim)
PHP route `schema` is the authoritative contract. Interim (until the emitter lands): CI runs a small reviewable script that extracts route `schema` keys (methods, paths, param/header names) from PHP and asserts the matching request YAMLs contain the same names — deterministic, no filename-mapping problem. Rejected: `bru import openapi` round-trip diff (import can't reproduce hand-written `docs:`/`assertions`/`tags`, filenames won't match conventions → permanent false positives) and OpenAPI Sync UI (5 syncs/mo OSS cap). The emitter follow-up is filed as SMO-142, which retires the interim seed + script on landing.

### 4. Auth: app passwords local-only; CI credential-free (lock kept)
Local: collection-level Basic `Authorization: Basic {{process.env.SMOOTH_BASIC_AUTH}}`, inherited by all requests; per-developer app passwords, revocable. CI: zero credentials — public `GET` reads plus 401 negatives (which need no credentials) run in `bruno-run`; authenticated reads and 403 cases are tagged `auth-local` and excluded from CI. Cookie+nonce flow rejected (scripted login + nonce scraping per run, brittle in CI). Explicit note: app passwords over runner-local/plain-HTTP `localhost` are acceptable for local/CI-loopback use only, never for hosted envs. Future option (not in scope): runtime-minted per-run passwords via `wp-env run` + `$GITHUB_ENV` — needs no stored secret and no lock change, but adds provisioning complexity; revisit if the auth matrix must run in CI.

### 5. Contract-style requests with an explicit tag table
Docs-tab template per request: Purpose → Auth → Parameters (each param/header carries a description) → Success example → Error cases → Notes/Changelog line. Assertions: status + key schema fields + content-type. Single CI filter: `--tags smoke` (no `--tests-only` — setup/seed requests without assertions must still run).

| File pattern | Tags | Runs in CI |
|---|---|---|
| `NN-<name>.yml` (reads, public) | `smoke` | yes |
| `*-unauthenticated.yml` (→ 401) | `smoke`, `auth` | yes (no creds needed) |
| `*-forbidden.yml` (→ 403), authed reads | `auth-local` | no (local only) |

No `money-path` Bruno tag — that name belongs to the GitHub PR-label machinery; if money endpoints need a CI subset later it gets a distinct tag. Minimal request shape (confirm exact keys against the OpenCollection structure reference at implementation — key names are version-sensitive):
```yaml
# menus/01-list-menus.yml (illustrative)
info:
  name: List menus
  seq: 1
  tags: [smoke]
request:
  method: GET
  url: "{{baseUrl}}/wp-json/smooth/v1/menus"
docs: |
  ## Purpose ...
assertions:
  - status == 200
```

### 6. CI: `bruno.yml` (eighth workflow), two jobs, event-based promotion
Same trigger filter as other workflows (`development`/`release/*` + dispatch). Jobs:
- `docs-guard` (no WP needed, always runs): endpoint PRs must touch `api-docs/` (git-diff path predicate, owned by this job); credential-literal + `secrets.*` grep over `api-docs/`; YAML lint.
- `bruno-run` (advisory, `continue-on-error: true`): boot wp-env → run this change's minimal inline menus seed → exact command `bru run --env ci --tags smoke --reporter-junit reports/bruno-junit.xml --reporter-html reports/bruno-report.html --reporter-skip-headers "Authorization" --reporter-skip-request-body` → upload both (30-day retention, `if: always()`).
Pre-condition: billing restoration proven by one green Actions run on `development` before `bruno-run` is enabled. `bru-version` pinned exact (`4.1.0` at plan time; re-verify at implementation — note: CLI npm version tracks the app major but pin by registry value, not assumption). Promotion rule (event-based): ≥5 consecutive green `bruno-run`s across ≥3 PRs, zero infra flakes, named sign-off, then remove `continue-on-error` + update delta spec + `BRANCH_PROTECTION.md` + required-checks list.

## Risks / Trade-offs

- [Risk] Billing lock blocks proving the CI job → Mitigation: billing restoration is a gating pre-task; `docs-guard` (WP-free) still adds value on day one.
- [Risk] No local Docker → implementer can't run the collection here → Mitigation: task 1.4 split — desktop-open verified locally, CLI run proven via `workflow_dispatch` artifact on the PR branch.
- [Risk] Endpoints (SMO-121) and fixtures (SMO-122) unstarted → Mitigation: skeleton + conventions now; contract requests gated on SMO-121; CI uses this change's inline seed until SMO-122 replaces it.
- [Risk] Interim seed + hand-written requests can agree while both wrong → Mitigation: seed is menus-paths-only, tiny, reviewed against route schemas line-by-line; emitter issue tracked with retirement condition.
- [Risk] YAML format churn → Mitigation: exact `bru-version` pin + re-verify at implementation; one-click migration path exists upstream.
- [Risk] Secret leakage in reports/logs → Mitigation: CI is credential-free by construction; secret grep covers committed files; `--reporter-skip-*` guards bodies/headers regardless.
- [Risk] Collection rots when endpoints change → Mitigation: `docs-guard` filename check + schema-extract gate, both in REPO-CI (no external service).
