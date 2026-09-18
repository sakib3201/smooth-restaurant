## Why

All seven CI workflows are `workflow_dispatch`-only, so the `composer quality` gate we got green can silently rot: nothing runs on PRs and nothing blocks merge. SMO-123 requires every check to run before merge, with migration and budget guards protecting the money path.

## What Changes

- Add `pull_request` triggers filtered to base branches `development` and `release/*` (plus `push` to those same branches for post-merge verification) on all seven workflows, keeping `workflow_dispatch` on each per the issue constraint. Feature/change branches get no CI until they open a PR against `development` or `release/*`.
- Trim the `php-lint.yml` PHP matrix from 8.1/8.2/8.3 to 8.2/8.3 to match the composer floor (`>=8.2`).
- Wire the official Plugin Check GitHub Action against the built ZIP in `build.yml`.
- Add a leak-check budget job: fail when Smooth assets load on non-Smooth pages (numeric KB caps deferred to SMO-104).
- Add migration guards: existing migration unit tests run in `php-tests.yml`, plus a warn-only version-lint job that annotates PRs touching `src/Database/` without bumping `MigrationRunner::TARGET_VERSION`.
- Add a `money-path` PR-label trigger: labeled PRs must pass the ledger/totals/migration suites before merge.
- Upload reviewer evidence: PHPUnit JUnit logs, coverage HTML, and the built plugin ZIP as workflow artifacts.
- Wire `e2e-tests.yml` triggers with `continue-on-error` until the first money-path Playwright spec exists (explicitly excluded from required checks).
- Document the branch-protection setup (exact required-check names) so the user can flip the switch in the repo admin UI.
- No secrets are introduced; all jobs run on public runners with no credentials.

## Capabilities

### New Capabilities

- `ci-gates`: pull-request CI contract — which workflows run on which events, required vs advisory jobs, budget-leak check, warn-only migration version lint, `money-path` label suites, evidence uploads, Plugin Check on the ZIP, and the branch-protection checklist.

### Modified Capabilities

- None. Existing specs (`provider-architecture`, `data-access`, `coding-standards`) gain CI enforcement but no requirement changes.

## Impact

- Affected: `.github/workflows/*.yml` (7 files), `composer.json` scripts only if a lint helper is needed, docs for branch protection.
- No runtime code changes (`src/`, `tests/` untouched except possibly a budget-leak test hook if the leak check needs one).
- CI minutes increase (full matrix per PR); E2E remains non-blocking until suites exist.
