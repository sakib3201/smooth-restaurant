## Context

Seven workflows under `.github/workflows/` (`quality`, `php-tests`, `php-lint`, `js-lint`, `js-tests`, `e2e-tests`, `build`) are all `workflow_dispatch`-only, each with a single job of the same family name. `composer quality` (cs → stan → test) is green locally; CI never verifies it. The `php-lint.yml` matrix is already `['8.2', '8.3']`, matching the composer floor — no trim needed. No secrets exist in any workflow; none are introduced. The user (Sakib) owns repo admin and will flip branch protection manually from documented steps.

## Goals / Non-Goals

**Goals:**
- Every PR gets the full gate automatically; failures block merge via required checks.
- Money-path PRs (label `money-path`) additionally gate on ledger/totals/migration suites.
- Migration safety gets a warn-only version lint plus the existing unit migration tests.
- Asset leaks (Smooth assets off Smooth pages) fail the build; numeric budgets stay with SMO-104.
- Reviewers get JUnit logs, coverage HTML, and the built ZIP as artifacts.

**Non-Goals:**
- E2E enforcement (no money-path Playwright specs exist yet; job runs soft-fail, non-required).
- Numeric asset KB budgets (SMO-104).
- Down-migrations or rollback automation.
- Self-hosted runners, concurrency tuning beyond cancel-in-progress, or secret management (nothing to manage).

## Decisions

- **Triggers: `pull_request` (opened/synchronize/reopened) filtered to base branches `development` + `release/*`, plus `push` to those branches, plus keep `workflow_dispatch`.** Only integration-bound PRs run the gate; pushes to feature/change branches run nothing. `push` to the trunk branches catches post-merge drift. Chosen over unfiltered PR triggers to keep minutes on the branches that matter, and over push-on-every-branch for the same reason. Prerequisite: the `development` branch does not exist yet — it must be created (from `rebuild`) before the triggers can fire.
- **All 7 workflows at once, not phased.** Each file is a one-job edit of the same `on:` block; splitting buys nothing and leaves the gate half-built.
- **E2E soft-fail via `continue-on-error` on the test step + explicit non-required status.** Alternative (skip the job entirely) hides the gap; soft-fail keeps the wp-env path exercised in CI while suites are authored. Required-check docs list `e2e-tests` as explicitly excluded.
- **Migration version lint as a warn-only job in `php-tests.yml`.** Shell script: `git diff --name-only origin/main...HEAD` for `src/Database/` changes without a `TARGET_VERSION` bump in `MigrationRunner.php` → `::warning::` annotation, exit 0. Fail-mode rejected by the user (version bumps sometimes legitimately ride with schema changes in follow-ups); warn keeps humans in the loop without false red.
- **Money-path gating via the `money-path` PR label.** A `money-path` job (in `php-tests.yml`) runs only when the label is present and runs the ledger/totals/migration suites (`--filter` over `TransactionIdempotency`, `Money`, `TotalsCalculator`, `MigrationRunner`, `Repositories` tests). Label chosen over path filters by the user (explicit human intent, no glob maintenance); full suite still runs on every PR regardless.
- **Leak check as a Jest test, not a shell grep.** `smooth_should_load()` contract already exists with unit tests; the CI budget job runs a jsdom/Jest assertion that non-Smooth markup enqueues zero Smooth handles. Numeric caps deferred to SMO-104 when real assets exist.
- **Plugin Check via the official `wordpress/plugin-check-action` on the `build.yml` ZIP artifact.** Closest to WordPress.org review; runs post-build so it checks the shippable artifact. Composer-binary alternative rejected (heavier local dep, same engine).
- **Evidence: JUnit XML (`--log-junit`), coverage HTML artifact, built ZIP.** `actions/upload-artifact@v4` in `php-tests.yml` (logs + coverage) and `build.yml` (ZIP already produced; add upload). Coverage upload is HTML (human review), not a coverage-gate percentage.
- **Branch protection documented, not coded.** Required checks to require on `development` (and `release/*`): `Full Quality Check (quality)`, `php-tests`, `php-lint`, `js-lint`, `js-tests`, `build`, Plugin Check job; explicitly NOT `e2e-tests`. Steps reference Settings → Branches → Add rule for the `development` branch pattern plus `release/*`.

## Risks / Trade-offs

- [Risk] E2E soft-fail normalizes red CI → Mitigation: job named clearly, non-required list documented, SMO follow-up flips it to required when first money-path spec lands.
- [Risk] Warn-only version lint gets ignored → Mitigation: annotation appears in PR Files-changed view; reviewers see it next to the diff.
- [Risk] Full 7-workflow fan-out per PR costs minutes → Mitigation: single-job workflows, existing caches (composer/npm/Playwright), cancel-in-progress concurrency on PRs.
- [Risk] Label-gated money suites forgotten on relevant PRs → Mitigation: PR template reminder line (add `money-path` label checkbox); full suite still runs regardless.

## Migration Plan

Deploy: create the `development` branch (from `rebuild`) if missing; merge the workflow PR; user enables branch protection per docs; verify with a trivial follow-up PR against `development` showing all checks green. Rollback: revert the single PR — workflows return to dispatch-only; no data or runtime impact either way.

## Open Questions

- None blocking. Minor: exact Plugin Check action version pin at implementation time; whether `push` branches should be `main`+`rebuild` or a `rebuild*` pattern (recommend the latter if release branches appear).
