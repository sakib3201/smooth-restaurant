## ADDED Requirements

### Requirement: Pull-request triggers on all workflows
Every workflow under `.github/workflows/` SHALL run on `pull_request` (opened, synchronize, reopened) whose base branch is `development` or matches `release/*`, and on `push` to those same branches, while retaining `workflow_dispatch`. PRs and pushes on feature/change branches SHALL NOT trigger any workflow.

#### Scenario: PR to development runs the gate
- **WHEN** a pull request targeting `development` is opened
- **THEN** all seven workflows (quality, php-tests, php-lint, js-lint, js-tests, e2e-tests, build) start automatically

#### Scenario: PR to a feature branch does not run the gate
- **WHEN** a pull request targets any branch other than `development` or `release/*`
- **THEN** no workflow starts automatically

#### Scenario: Push to a feature branch does not run the gate
- **WHEN** commits are pushed to a feature/change branch
- **THEN** no workflow starts automatically

#### Scenario: Manual dispatch preserved
- **WHEN** a maintainer triggers any workflow via `workflow_dispatch`
- **THEN** the workflow runs with identical steps and gates as the PR run

### Requirement: Required checks block merge
The quality, php-tests, php-lint, js-lint, js-tests, asset-budget, and build jobs (build includes the Plugin Check step) SHALL be listed as required status checks; a PR with any required check failing SHALL NOT be mergeable.

#### Scenario: Failing lint blocks merge
- **WHEN** PHPCS fails on a PR
- **THEN** the merge button is blocked until a green re-run

#### Scenario: E2E excluded from required checks
- **WHEN** branch protection is configured per the documented checklist
- **THEN** `e2e-tests` is NOT in the required list and its soft-fail never blocks merge

### Requirement: E2E soft-fail until suites exist
The `e2e-tests.yml` Playwright step SHALL use `continue-on-error` and the job SHALL be non-required until the first money-path Playwright spec lands.

#### Scenario: Missing specs do not block
- **WHEN** the Playwright suite has no money-path specs and the run fails
- **THEN** the workflow concludes neutral-to-failure but the PR remains mergeable

### Requirement: Migration version lint (warn-only)
A job in `php-tests.yml` SHALL detect PRs that change files under `src/Database/` without bumping `MigrationRunner::TARGET_VERSION` and emit a `::warning::` annotation without failing the build.

#### Scenario: Schema change without bump warns
- **WHEN** a PR modifies a repository schema and `TARGET_VERSION` is unchanged
- **THEN** the PR shows a warning annotation and the job still passes

#### Scenario: Schema change with bump is silent
- **WHEN** a PR modifies a repository schema and bumps `TARGET_VERSION`
- **THEN** no warning is emitted

### Requirement: Money-path label suites
PRs carrying the `money-path` label SHALL run the ledger/totals/migration test filter (`TransactionIdempotency`, `Money`, `TotalsCalculator`, `MigrationRunner`, `Repositories` suites) as a required job; PRs without the label SHALL skip it.

#### Scenario: Labeled PR runs money suites
- **WHEN** a PR has the `money-path` label
- **THEN** the money-path job runs and its failure blocks merge

### Requirement: Asset leak check
CI SHALL fail a PR when Smooth scripts or styles enqueue on pages without Smooth content (0KB leak tolerance); numeric KB budgets remain out of scope until SMO-104.

#### Scenario: Leak fails the build
- **WHEN** a non-Smooth page enqueues a `smooth-*` asset in the leak test
- **THEN** the budget job fails and blocks merge

### Requirement: Plugin Check on the built ZIP
`build.yml` SHALL run the official Plugin Check action against the packaged plugin ZIP after a successful build.

#### Scenario: Check findings fail the build
- **WHEN** Plugin Check reports errors on the ZIP
- **THEN** the build job fails and blocks merge

### Requirement: Reviewer evidence uploads
`php-tests.yml` SHALL upload PHPUnit JUnit logs and coverage HTML; `build.yml` SHALL upload the plugin ZIP via `actions/upload-artifact@v4`.

#### Scenario: Artifacts present on PR run
- **WHEN** a PR CI run completes
- **THEN** the run exposes downloadable JUnit logs, coverage HTML, and the plugin ZIP

### Requirement: No secrets in workflows
No workflow SHALL reference secrets, credentials, or authenticated pushes; all jobs SHALL run on public runners without credentials.

#### Scenario: Secret scan clean
- **WHEN** workflows are grepped for `secrets.`
- **THEN** zero matches are found
