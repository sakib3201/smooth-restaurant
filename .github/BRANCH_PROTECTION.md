# Branch protection — Smooth Restaurant

Applies to the `development` branch and the `release/*` pattern.
CI workflows only fire on PRs targeting these branches.

## Setup (repo admin)

1. GitHub → Settings → Branches → Add branch protection rule.
2. Branch name pattern: `development`. Repeat for `release/*`.
3. Check **Require a pull request before merging**.
4. Check **Require status checks to pass before merging**.
5. Add these required checks (exact job names — steps cannot be required individually):
   - `Full Quality Check` (from `quality.yml`)
   - `PHP Tests` (from `php-tests.yml`)
   - `Money-Path Suites` — required only when the `money-path` label is present; GitHub treats skipped conditional jobs as passing, so list it as required anyway
   - `PHP Lint (PHP 8.2)` and `PHP Lint (PHP 8.3)` (from `php-lint.yml` matrix)
   - `JS Lint` (from `js-lint.yml`)
   - `JS Tests` (from `js-tests.yml`)
   - `Asset Budget (leak check)` (from `js-tests.yml`)
    - `Build Plugin` (from `build.yml` — includes the Plugin Check step, so Plugin Check failures block via this job)
    - `Bruno Docs Guard` (from `bruno.yml` — WP-free, required from day one)
  6. Explicitly do NOT require `E2E Tests` — it runs soft-fail
    (`continue-on-error`) until the first money-path Playwright spec
    exists. Flip it to required when that spec lands.
  6b. Explicitly do NOT require `Bruno Collection Run (advisory)` — it runs
    soft-fail until the event-based promotion rule is met (>=5 consecutive
    greens across >=3 PRs, zero infra flakes, named sign-off).
7. Optionally check **Require branches to be up to date before merging**.

## Notes

- `Migration Version Lint` is advisory by design (`::warning::`
  annotations, never red). Do not add it to required checks.
- Workflows also run on `push` to `development` / `release/*` and stay
  available via `workflow_dispatch`. Feature branches run nothing until
  they open a PR against `development` or `release/*`.
