# Agent Notes — Smooth Restaurant

> WordPress plugin (restaurant management). PHP 8.1+, WP 6.4+, Node 20+, npm 10+.
>
> **Rebuild status (2026-09-14):** this branch was reset to a clean harness — skills, tooling, test scaffolding and docs only. All implementation is being rebuilt ground-up, driven by the Linear project (see below).

## Architecture

- **Entrypoint**: `smooth-restaurant.php` — defines `SR_VERSION`, `SR_PLUGIN_DIR`, `SR_PLUGIN_URL`, `SR_PLUGIN_BASENAME`, registers activation/deactivation hooks, and boots `SmoothRestaurant\Core\Plugin::instance()->boot()` on `plugins_loaded`.
- **DI container**: `src/Core/Container.php` — lightweight auto-wiring container. Service providers extend `ServiceProvider` and are registered in `Plugin::registerProviders()`.
- **Namespace**: `SmoothRestaurant\` under `src/` (PSR-4 autoloaded via Composer and a custom `spl_autoload_register` fallback in the main file).
- **Current providers**: none wired yet. Rebuild issues add providers under `src/Providers/` and register them in `Plugin::registerProviders()`.

## Build & Dev

- `npm run dev` — starts `@wordpress/scripts` with hot reload (`--hot`).
- `npm run build` — builds three separate webpack bundles:
  - `build:admin` from `assets/src/admin` → `assets/build/admin`
  - `build:frontend` from `assets/src/frontend` → `assets/build/frontend`
  - `build:blocks` from `assets/src/blocks` → `assets/build/blocks`
- `npm run i18n` — generates `.pot` and `.json` translation files under `languages/`.
- Tailwind CSS is used with prefix `sr-` and custom color `sr-admin`. Config: `tailwind.config.js` / `postcss.config.js`.
- TypeScript path aliases (also mapped in Jest and webpack):
  - `@/admin/*` → `assets/src/admin/*`
  - `@/frontend/*` → `assets/src/frontend/*`
  - `@/shared/*` → `assets/src/shared/*`
  - `@/blocks/*` → `assets/src/blocks/*`
- **shadcn/ui** — rebuild issues re-initialize components under `assets/src/admin/components/ui/` using `@base-ui/react` primitives with `base-nova` style. CSS variables live in `assets/css/admin-global.scss`. Component alias: `@/admin/components/ui/*`.

## Testing

- **PHPUnit** (`composer test`)
  - `composer test:unit` — `phpunit --testsuite Unit`
  - `composer test:integration` — `phpunit --testsuite Integration`
  - `composer test:wp-env` — runs PHPUnit inside the `@wordpress/env` container
  - Bootstrap: `tests/bootstrap.php` (looks for `WP_TESTS_DIR` env var, falls back to `vendor/wordpress/wordpress/tests/phpunit`).
- **Jest** (`npm run test:js`) — uses `jest.config.js` with `jsdom`, `ts-jest`, and `tests/js/setupTests.ts` (mocks `@wordpress/i18n`).
- **Playwright E2E** (`npm run test:e2e`)
  - `playwright.config.ts` at repo root; `tests/e2e/playwright.config.ts` re-exports it.
  - `workers: 1`, `fullyParallel: false` — WordPress state is shared.
  - Global setup/teardown in `tests/e2e/fixtures/index.ts` starts/stops `wp-env` automatically.
  - Base URL: `http://localhost:8888`.
  - Requires Docker for `@wordpress/env`.

## Lint & Static Analysis

- **PHP**: `composer cs:check` / `composer cs:fix` (PHPCS with WordPress standard). `composer stan` (PHPStan level 8, memory limit 1G).
- **JS/TS**: `npm run lint:js` (ESLint via `@wordpress/scripts`). `npm run lint:css` (Stylelint).
- **Format**: `npm run format` (Prettier via `@wordpress/scripts`).
- **Quality gate**: `composer quality` runs `cs:check` → `stan` → `test`.

## Environment

- `.wp-env.json` — PHP 8.1, WP 7.0, plugin mounted at `.`, theme `twentytwentyfour`, ports `8888` / `8889`.
- `npm run env:start` / `env:stop` / `env:clean` — `@wordpress/env` wrappers.

## Release Packaging

Build workflow (`.github/workflows/build.yml`) creates a ZIP by rsync-excluding:
`node_modules`, `vendor`, `tests`, `.git`, `.github`, `*.config.js`, `*.config.json`, `composer.*`, `package*`, `phpcs.xml*`, `phpstan.neon*`, `playwright.config.*`, `jest.config.*`, `.eslint*`, `.stylelint*`, `.prettier*`, `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`.

## CI Workflows

All workflows are `workflow_dispatch` only:
- `quality.yml` — full gate (PHPCS, PHPStan, PHPUnit unit + integration, ESLint, Stylelint, format check, Jest, build).
- `php-tests.yml` — PHPUnit unit + integration with coverage artifact.
- `e2e-tests.yml` — Playwright against `wp-env`.
- `build.yml` — build artifacts + plugin ZIP.
- `php-lint.yml` — PHPCS + PHPStan across PHP 8.1/8.2/8.3 matrix.
- `js-lint.yml` — ESLint + Stylelint + format check.
- `js-tests.yml` — Jest only.

## Key Files

- `smooth-restaurant.php` — main plugin file (also scanned by PHPStan).
- `src/Core/Plugin.php` — singleton bootstrap.
- `src/Core/Container.php` — DI container.
- `assets/src/admin/index.tsx` — admin entrypoint placeholder (rebuild issue replaces it).
- `assets/src/frontend/index.ts` — frontend entrypoint placeholder (rebuild issue replaces it).
- `phpunit.xml.dist` — two test suites: Unit / Integration.
- `phpstan.neon.dist` — level 8, scans `src/`, `tests/`, `smooth-restaurant.php`, `uninstall.php`.
- `jest.config.js` — extends `@wordpress/scripts/config/jest-unit.config`, adds path aliases, `ts-jest` and `passWithNoTests`.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, invoke the `skill` tool with `skill: "graphify"` before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

## Linear (source of truth)

This project is driven from Linear via the `linear` MCP server (`linear_*` tools). Workflow rules:

- **Linear leads, the board mirrors.** Milestones/issues live in Linear; agents sync issue state and post progress comments back with `linear_*` tools. Local OpenSpec changes and kanban boards are ephemeral execution state.
- **No build starts without a short spec** attached to the Linear issue. Pull the issue + spec before writing code; link every subtask back to its parent issue.
- **Branch/worktree per issue**; max 2 in flight. Open a worktree with `git worktree add .worktrees/<issue-slug> -b <issue-slug>`.
- **On completion:** archive the OpenSpec change, then update the Linear issue status + comment with test evidence (gates green: `composer quality`, `npm run test:js`, `npm run test:e2e`).
- **Money-path rules:** ledger is append-only (refunds are new rows); totals/ledger/webhook/migration changes require matching tests in the same PR.
