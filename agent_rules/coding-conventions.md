# Coding Conventions

> Scope: all PHP/TS contributed to this repo. Lint is the enforcer
> (`composer cs:check`, `composer stan`, `npm run lint:js`); this file is
> the rationale behind the rules.

## PHP

- `declare(strict_types=1)` in every file. PHP 8.2 idioms allowed
  (readonly classes, enums, first-class callables); floor is PHP 8.2 / WP 6.8.
- PSR-12 base + targeted WP sniffs (see `phpcs.xml.dist`). Prefer the
  auto-fixer (`composer cs:fix`) over hand-formatting.
- PHPStan level 8 must stay clean. Add `assert()` narrowings or precise
  `@var` annotations instead of new ignores; stale ignores fail the build.

## Architecture discipline (review-enforced, test-enforced)

- Provider `register()` binds ONLY: no hooks, no DB, no i18n.
  Provider `boot()` hooks ONLY, first line bails outside its context, and
  in-context boots call `markBooted()`. See `src/Providers/AGENTS.md`.
- Hook and filter names use the `smooth_restaurant_` prefix in underscore
  style (`smooth_restaurant_menu_saved`), never dotted names. `HOOKS.md`
  tracks every owned hook.
- `$wpdb` access lives exclusively in `src/Database/` (guarded by the
  no-postmeta architecture test). Domains stay pure; WP calls in new
  shared code MUST be `function_exists`-guarded for unit context.
- Money is `Money` cents-only (`src/Domains/Shared/Money.php`) — floats
  enter only via the explicit `fromFloat()` factory. Ledger tables are
  append-only; refunds are new rows.
- Repository columns: no `DEFAULT ''` under a UNIQUE key, no zero-date
  defaults (strict-mode MySQL). Index hot paths with composite KEYs.

## TypeScript / assets

- Path aliases `@/admin`, `@/frontend`, `@/shared`, `@/blocks` (mapped in
  `jest.config.js` and webpack). Tailwind prefix `sr-`.
- Frontend bundle gates on `hasSmoothContent()` (`assets/src/shared/`);
  numeric budgets arrive with SMO-104, the 0KB leak rule applies now.

## Docs that behave like code

- `HOOKS.md` gains a row for every owned filter/action added.
- Keep per-directory `AGENTS.md` next to the code it describes; root
  `AGENTS.md` is a router only — never inline domain detail there.
