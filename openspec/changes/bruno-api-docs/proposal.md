## Why

`sm_docs/16-technical-details.md` §13.6 locks a docs pipeline (Bruno collections in-repo, `openapi.json` per tag, CI cross-checks), but none of its scaffolding exists: no `api-docs/`, no generation jobs, no enforcement. SMO-121 will ship the first public endpoints (`GET /smooth/v1/menus`) with a "documented schema" acceptance criterion and nothing to document them in. Building the Bruno track now gives SMO-121 a target to hit.

## What Changes

- Add `api-docs/` collection at repo root in **OpenCollection YAML** format (Bruno v4 recommendation for new collections; see design for the §13.6 `.bru` amendment).
- Scaffold the `smooth-v1` collection now: environments, conventions, Docs-tab template, skeleton folders per domain. Full contract requests for menus ride with SMO-121 when its endpoints land (no speculative contracts).
- Ship `local` (wp-env) + `ci` (wp-env in runner) environments; WordPress Application Passwords for local use only via collection-root `.env` (gitignored) + `.env.sample`. **CI stays credential-free**: the zero-secrets lock is kept — CI runs public reads + 401 negatives only; the authenticated/403 matrix is local-only (tagged `auth-local`).
- Establish conventions: numbered kebab-case files (`menus/01-list-menus.yml`), `Verb resource` request names, `seq` ordering, Docs-tab template (purpose/auth/params/errors/examples), descriptions on params and variables, tag table (`smoke` = CI subset incl. 401 negatives; `auth-local` = excluded from CI).
- Flow direction: PHP route `schema` stays authoritative → generates `openapi.json` → CI cross-checks the collection against it (CLI-based wth a deterministic schema-extract script, not `bru import` diff, not Sync UI). The WP-native OAS emitter is filed as a tracked follow-up; a hand-maintained `api-docs/openapi.json` seed (menus paths only) is explicitly interim with a retirement condition.
- Add `bruno.yml` CI workflow on PRs to `development`/`release/*` (eighth workflow): a WP-free `docs-guard` job (filename check, secret grep, YAML lint) plus an advisory `bruno-run` job (boots wp-env, seeds via this change's minimal inline menus seed, runs `bru` with JUnit upload). `bruno-run` is gated on billing restoration and graduates to required by an event-based rule.
- Amend `sm_docs §13.6` (`.bru` → OpenCollection YAML) as part of this change so the locked spec matches reality. The `ci-gates` no-secrets lock is NOT amended — this plan complies with it.

## Capabilities

### New Capabilities

- `bruno-api-docs`: collection location/format, naming and authoring conventions, environments and local-only secrets handling, Docs-tab template, assertion/tag standards, schema-authoritative flow with interim seed + emitter follow-up, public-only CI run and event-based promotion.

### Modified Capabilities

- `ci-gates`: adds the Bruno `docs-guard` + advisory `bruno-run` jobs (eighth workflow), JUnit evidence, and the event-based promotion rule. No change to the no-secrets requirement.

## Impact

- New: `api-docs/` tree (collection config, environments, requests, `.env.sample`, `.gitignore` entries), `.github/workflows/bruno.yml`, interim `api-docs/openapi.json` seed, minimal inline CI seed script.
- Docs: `sm_docs/16-technical-details.md` §13.6 amendment (format lock `.bru` → OpenCollection YAML).
- Follow-up filed (not built here): WP-native OAS emitter issue, which retires the interim seed.
- No runtime plugin code changes; no secrets or `secrets.*` references committed. CI minutes increase by one wp-env-boot job per PR (same trigger filter as other workflows).
