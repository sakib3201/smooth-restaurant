## Why

The rebuild boots from a thin `Plugin` singleton + auto-wiring `Container`, but `registerProviders()` is empty, the container conflates singleton instances with provider objects, and the lint ruleset enforces pure WordPress standards against the locked `sm_docs/16 §13.1` hybrid (PSR-12 base + WP-idiomatic seams). Without a locked provider architecture now, every M1 domain (menu, cart, checkout, orders, payments) will invent its own wiring and reintroduce global-load performance costs.

## What Changes

- Lock the service-provider boot lifecycle: thin entry → `Plugin` owns `Container` + provider list → providers bind in `register()` (no side effects) and hook in `boot()` with context-gated early bail.
- Fix the container seam: split singleton instances from provider objects, add `has()`/`instance()`, keep reflection at boot only with closure/singleton bindings on hot paths.
- Lock the Free/Pro extension seam: `smooth_service_providers` filter carries the provider list, Pro registers additive providers against `Contracts/` with version-floor check, never forks Free.
- Define the M1 provider/module map (one provider per bounded context) and the per-request boot matrix (frontend diner vs checkout POST vs admin vs REST vs cron).
- Switch the lint contract to PSR-12 base + targeted WP sniffs + Plugin Check, matching `sm_docs/16 §13.1` (resolves current `phpcs.xml.dist` drift that disables ~10 WP rules to tolerate OO code).
- Sweep the compatibility floor to WordPress 6.8+ / PHP 8.2+ everywhere (plugin headers, `composer.json`, PHPCS `testVersion`, wp-env matrix, `readme.txt`, CI matrix); new code may use PHP 8.2 idioms.
- Define the custom-table access pattern: shared `BaseRepository` over `$wpdb` (prefix, charset/collate, prepare, typed rows), dbDelta-first schema rule, Pro reads via Free repository interfaces and writes only its own tables.
- Guard the no-postmeta invariant with a PHPUnit architecture test over transactional domains (menu CPT read-only mirror excepted).

## Capabilities

### New Capabilities

- `provider-architecture`: provider lifecycle, container contract, Free/Pro seam, context-gated boot matrix, migration/asset provider roles, WP 6.8+ / PHP 8.2+ floor.
- `data-access`: `BaseRepository` pattern, dbDelta-first schema rule, Pro read/write boundaries, no-postmeta architecture guard.
- `coding-standards`: PSR-12 base with WP-idiomatic seams (hook naming, caps, nonces, i18n), strict types, lint enforcement in CI.

### Modified Capabilities

- None (greenfield rebuild; no existing specs under `openspec/specs/`).

## Impact

- Affected code: `smooth-restaurant.php` (stays thin), `src/Core/` (`Plugin`, `Container`, `ServiceProvider`, `Activator`/`Deactivator`, future `MigrationRunner`), `src/Providers/`, `src/Contracts/`, `src/Domains/`, `phpcs.xml.dist`, CI quality gate.
- APIs: new `smooth_service_providers` filter contract; provider `register()`/`boot()` discipline becomes review-enforced.
- Dependencies/systems: aligns with locked `@wordpress/scripts` build (`.asset.php` deps), `smooth_should_load()` gate, `smooth_db_version` migrations; no runtime dependency added.
