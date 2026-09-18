## ADDED Requirements

### Requirement: Bruno docs-guard job
CI SHALL run a WP-free `docs-guard` job on PRs to `development`/`release/*`: endpoint PRs must touch `api-docs/` (git-diff path predicate), credential-literal and `secrets.*` grep over `api-docs/` passes, and YAML lint passes. This job is required from day one and needs no billing-gated runner resources beyond a standard job.

#### Scenario: Endpoint PR without docs
- **WHEN** a PR adds `register_rest_route` without touching `api-docs/`
- **THEN** `docs-guard` fails, naming the missing collection update

### Requirement: Bruno collection run (advisory, then required)
CI SHALL run the `api-docs/smooth-v1/` collection via a `bruno-run` job (making `bruno.yml` the eighth workflow): boot wp-env, run this change's minimal inline menus seed, execute `bru run --env ci --tags smoke` with JUnit and HTML reporters, and upload both as reviewer evidence (30-day retention, `if: always()`). The job SHALL start advisory (`continue-on-error: true`) and SHALL NOT be enabled until billing restoration is proven by one green Actions run on `development`.

#### Scenario: Bruno job fails while advisory
- **WHEN** a smoke assertion fails on a PR
- **THEN** the workflow reports failure and uploads the report, but merge is not blocked

### Requirement: Event-based promotion to required
Promotion SHALL require ≥5 consecutive green `bruno-run`s across ≥3 PRs with zero infra flakes plus a named sign-off, followed by removing `continue-on-error` and updating this spec, `BRANCH_PROTECTION.md`, and the required-checks list.

#### Scenario: Promotion threshold met
- **WHEN** the fifth consecutive green run lands across a third PR with no infra flakes
- **THEN** the owner removes `continue-on-error` and updates the three promotion sites in the same PR

### Requirement: Credential-free Bruno CI (lock kept)
`bruno.yml` SHALL contain no `secrets.*` references and no credential values; reports SHALL exclude `Authorization` headers and request bodies via `--reporter-skip-*` guards. The existing no-secrets requirement is unchanged.

#### Scenario: Authenticated run evidence uploaded
- **WHEN** any Bruno job completes
- **THEN** the uploaded JUnit/HTML contain pass/fail evidence with no credential material, and the secret grep stays green
