## 1. Collection scaffold

- [x] 1.1 Create `api-docs/smooth-v1/` with `opencollection.yml` (name; default environment `local` set via Collection > Presets UI, never hand-edited), `environments/local.yml` + `environments/ci.yml` (lowercase names; base-URL vars, `{{process.env.*}}` secret refs with descriptions in `local` only)
- [x] 1.2 Add collection-root `.env.sample` (secret names only: `SMOOTH_BASIC_AUTH`, base-URL override), gitignore `api-docs/smooth-v1/.env`, set collection-level Basic auth inheriting to all requests
- [x] 1.3 Create domain folders `menus/ orders/ reservations/ tables/ qr/ system/` with folder-level docs; copy the Docs-tab template to `api-docs/smooth-v1/docs/request-template.md` (filename + `name` + `seq` triple example included)
- [ ] 1.4a Verify the collection opens in Bruno desktop with `local` selected by default (no server needed; passes on this machine)
- [ ] 1.4b Verify `bru run --env local` end-to-end via `workflow_dispatch` on the PR branch with the run artifact attached (wp-env cannot boot on this machine)

## 2. Menus contract (gated on SMO-121)

- [ ] 2.1 Skeleton `menus/` folder + folder docs now; author `menus/01-list-menus.yml` + `02-get-menu.yml` (contract style: docs, status + schema + content-type asserts, `smoke` tag, `seq` ordering) only when SMO-121 endpoints land — no speculative contracts
- [ ] 2.2 Author `auth` negative cases: `*-unauthenticated.yml` (→ 401, tags `smoke` + `auth`) and `*-forbidden.yml` (→ 403, tag `auth-local`, CI-excluded)
- [ ] 2.3 Hand-maintain interim seed `api-docs/openapi.json` (menus paths only) line-reviewed against SMO-121 route schemas

## 3. Schema-extract cross-check + emitter follow-up

- [x] 3.1 File the WP-native OAS-emitter Linear issue (SMO-142); reference its ID here and in design §Decisions-3 (tracks retirement of the interim seed + script)
- [ ] 3.2 Add the deterministic schema-extract script (route `schema` keys from PHP → assert matching request YAMLs carry the same method + path + param/header names; drift fails naming request + field)
- [ ] 3.3 Extend `docs-guard` (task 4.1): endpoint PRs must touch `api-docs/` (git-diff path predicate) + credential-literal/`secrets.*` grep + YAML lint

## 4. CI run (`bruno.yml`, eighth workflow)

- [ ] 4.0 Pre-condition: prove billing restoration with one green Actions run on `development` before enabling `bruno-run`
- [x] 4.1 New workflow on PRs to `development`/`release/*` (+ dispatch) with `docs-guard` (WP-free, required day one) and `bruno-run` (`continue-on-error: true`): boot wp-env, verify migrated baseline via `smooth_db_version` (no menu tables exist yet — SMO-121 adds `seed-menus.php` against its write path; SMO-122 replaces it when ready), exact command `bru run --env ci --tags smoke --reporter-junit reports/bruno-junit.xml --reporter-html reports/bruno-report.html --reporter-skip-headers "Authorization" --reporter-skip-request-body`, upload both (30-day retention, `if: always()`)
- [x] 4.2 Pin `bru-version` exact (`4.1.0` at plan time; re-verify against npm at implementation — CLI version tracks app major but pin by registry value), `ubuntu-24.04` runner; confirm zero `secrets.*`/credential material in workflow, logs, and uploaded reports
- [ ] 4.3 Promote to required per the event-based rule (≥5 consecutive greens across ≥3 PRs, zero infra flakes, named sign-off; update delta spec + `BRANCH_PROTECTION.md` + required-checks list in the same PR)

## 5. Spec alignment

- [x] 5.1 Amend `sm_docs/16-technical-details.md` §13.6 (`*.bru` → OpenCollection YAML default; Sync-UI rejection; public-only CI; interim seed + emitter follow-up)
- [x] 5.2 Validate change: `openspec validate bruno-api-docs`
