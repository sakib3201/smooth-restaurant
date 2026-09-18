## ADDED Requirements

### Requirement: Collection location and format
The repository SHALL carry the API collection at `api-docs/smooth-v1/` in OpenCollection YAML format, with collection config (`opencollection.yml`, default environment `local` set via Collection > Presets UI), `environments/local.yml` + `environments/ci.yml`, per-domain folders, and `.env.sample` (names only, never values) at the collection root.

#### Scenario: Fresh clone opens in Bruno
- **WHEN** a developer opens `api-docs/smooth-v1/` in Bruno with no local `.env`
- **THEN** the collection loads, selects the `local` environment, and every secret field shows empty with `.env.sample` as the fill-in guide

### Requirement: Naming and ordering conventions
Request files SHALL be numbered kebab-case (`<domain>/NN-short-name.yml`, e.g. `menus/01-list-menus.yml`), request names SHALL be `Verb resource` (e.g. `List menus`), and `seq` SHALL order chained requests (create → get → update).

#### Scenario: New endpoint request added
- **WHEN** an author adds `menus/03-update-menu.yml` with name `Update menu` and the next `seq`
- **THEN** it sorts after `02-get-menu.yml` in UI and CLI runs without renaming existing files

### Requirement: Docs-tab contract template
Every request SHALL document Purpose, Auth, Parameters (each param/header carries a description), one success example, error cases, and a Notes/Changelog line. Docs are Markdown and feed generated HTML output.

#### Scenario: Review of a new request
- **WHEN** a PR adds a request missing error cases or a parameter description
- **THEN** review rejects it under the Docs-PR rule before merge

### Requirement: Assertion and tag standards
Every executable request SHALL assert HTTP status plus key schema fields and content-type. Permission-sensitive requests SHALL include 401 (unauthenticated) cases; 403 and authenticated-read cases SHALL be tagged `auth-local` and excluded from CI. CI SHALL filter with `--tags smoke` only.

#### Scenario: CI smoke run
- **WHEN** CI runs `bru run --env ci --tags smoke`
- **THEN** public reads and 401 negatives execute, `auth-local` requests are skipped, and any assertion failure fails the run

### Requirement: Environments and local-only secrets handling
`local.yml` SHALL target wp-env (`http://localhost:8889` default, overridable), `ci.yml` SHALL target the runner's wp-env with no secret references, and local secrets SHALL flow only via `api-docs/smooth-v1/.env` → `{{process.env.*}}` interpolation. `.env` SHALL be gitignored; committed files SHALL contain no credential values and no `secrets.*` references.

#### Scenario: Secret scan on PR
- **WHEN** a PR touches `api-docs/`
- **THEN** CI greps for credential literals and `secrets.*` references and fails on any hit

### Requirement: Schema-authoritative flow with interim seed
PHP route `schema` SHALL remain the authoritative contract. Until the tracked OAS-emitter follow-up lands, an interim hand-maintained `api-docs/openapi.json` seed (menus paths only) SHALL exist, and CI SHALL run a deterministic script extracting route schema keys (methods, paths, param/header names) from PHP and asserting the matching request YAMLs contain the same names — drift fails the job. The seed and script SHALL retire when the emitter lands and its cross-check passes.

#### Scenario: Route gains a parameter
- **WHEN** a PR adds a query parameter to a route schema without updating the matching request
- **THEN** the cross-check job fails, naming the drifted request and field

### Requirement: Auth via Application Passwords (local only)
Local collection-level auth SHALL be Basic (`Authorization: Basic {{process.env.SMOOTH_BASIC_AUTH}}`, inherited by all requests) with per-developer app passwords. CI SHALL use zero credentials: only public reads and 401 negatives run there.

#### Scenario: Local authenticated run
- **WHEN** a developer fills `.env` from `.env.sample` and runs the collection with `--env local`
- **THEN** management requests return 2xx and the secret value never appears in committed files
