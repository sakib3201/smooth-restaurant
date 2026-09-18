## ADDED Requirements

### Requirement: PSR-12 base style

All PHP under `src/` SHALL follow PSR-12 with `declare(strict_types=1)` and strongly typed signatures, enforced by PHPCS in CI.

#### Scenario: Style gate fails the PR

- **WHEN** a PR adds untyped signatures or missing strict types in `src/`
- **THEN** the PHPCS gate fails before tests run

### Requirement: WordPress-idiomatic seams

Hook and filter names SHALL use the `smooth_*` prefix with documented PHPDoc (`@since`, `@param`, `@return`, `@example`); capability checks, nonces, and i18n functions SHALL follow WordPress conventions at every integration seam.

#### Scenario: Undocumented hook rejected

- **WHEN** a PR adds `apply_filters('smooth_*')` without a DocBlock example
- **THEN** review/CI flags the missing documentation

### Requirement: Single lint contract in CI

`phpcs.xml.dist` SHALL encode PSR-12 base plus targeted WordPress sniffs (hooks, naming, capabilities, nonces, i18n) plus Plugin Check, replacing the current pure-WordPress ruleset and its OO-tolerance disables with one `cs:fix` pass scoped to `src/`.

#### Scenario: Clean gate on rebuild scope

- **WHEN** `composer cs:check` runs after the fix pass
- **THEN** `src/` passes with zero errors and no OO-tolerance disables remain
