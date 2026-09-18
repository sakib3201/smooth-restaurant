# Testing & Quality Gates

> Full gate: `composer quality` (cs → stan → test) plus JS gates below.
> Evidence before assertions — always.

## PHP

- `composer test:unit` — PHPUnit Unit suite (no WordPress; stubbed via
  `tests/Support/WpStubs.php`). Primary gate, must stay green.
- `composer test:integration` — Integration suite; needs wp-env.
- `composer test:wp-env` — PHPUnit inside `@wordpress/env` (needs Docker).
- `composer cs:check` / `cs:fix`, `composer stan` (PHPStan L8, 1G).
- Money-path rule: ledger/totals/migration changes ship matching tests
  (see `linear-openspec-workflow.md`).

## JS/TS

- `npm run test:js` — Jest (jsdom, ts-jest). `npm run lint:js`
  (ESLint), `npm run lint:css` (Stylelint), `npm run format` (Prettier check).
- `npm run build` — three bundles (admin/frontend/blocks). A
  `package-lock.json` is committed: use `npm ci` in CI, never `npm install`.
- `npm run test:e2e` — Playwright, `workers: 1` (shared WP state),
  needs Docker + `wp-env`. E2E is soft-fail in CI until the first
  money-path spec exists.

## CI (`.github/workflows/`, see `.github/AGENTS.md`)

- Triggers: PRs targeting `development` / `release/*`, pushes there,
  plus `workflow_dispatch`. Feature branches are silent by design.
- Required checks exclude `e2e-tests`; migration version lint is
  advisory (`::warning::`). Setup: `.github/BRANCH_PROTECTION.md`.
- Reviewer evidence per PR: JUnit logs, coverage HTML, plugin ZIP
  (uploaded automatically), plus wp-env smoke output when relevant.

## Claiming completion

- Never claim green without running the gate. Quote counts
  (tests/assertions), not adjectives.
- If a gate cannot run here (no Docker, billing lock), say exactly
  which one and what substitute evidence exists.
