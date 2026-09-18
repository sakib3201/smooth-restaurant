## 1. Container seam

- [x] 1.1 Split `Container` instance store from provider store, add `has()`/`instance()`, keep auto-wiring as boot fallback
- [x] 1.2 Add container unit tests (singleton identity, test-double override, unresolvable dependency error)

## 2. Compatibility floor

- [x] 2.1 Sweep WP 6.8+ / PHP 8.2+ across headers, `composer.json`, PHPCS `testVersion`, wp-env matrix, `readme.txt`, CI matrix; allow PHP 8.2 idioms in new code
- [ ] 2.2 Smoke-boot wp-env on the new floor (single + multisite) as evidence — BLOCKED: no Docker in this environment; static substitute done (multisite loop unit test + version grep clean)

## 3. Provider lifecycle

- [x] 3.1 Wire `Plugin::registerProviders()` with the M1 Free provider list behind the `smooth_service_providers` filter plus class/interface/version-floor validation
- [x] 3.2 Scaffold `src/Providers/`, `src/Contracts/`, `src/Domains/` shells with `register()`-pure / `boot()`-gated discipline and boot-matrix smoke test
- [x] 3.3 Add provider lifecycle tests (register adds no hooks, boot bails outside context, invalid Pro entry skipped + logged)

## 4. Platform providers

- [x] 4.1 Implement `MigrationRunner` contract (`smooth_db_version`, version-guarded idempotent ordering, network-wide loop) and canonicalize `uninstall.php` key
- [x] 4.2 Implement Assets provider (`smooth_should_load()` + `.asset.php` deps + per-context enqueue, zero assets off-Smooth)
- [ ] 4.3 Verify boot-matrix smoke in wp-env (diner, checkout POST, admin, REST, cron) with no global enqueue leak — BLOCKED: no Docker; unit boot-matrix (25 tests) green as substitute

## 5. Data access

- [x] 5.1 Implement shared `BaseRepository` ($wpdb prefix/prepare/hydration) plus per-table repository shells and dbDelta-first schema rule
- [x] 5.2 Add no-postmeta PHPUnit architecture test over transactional domains (menu CPT read-only mirror excepted)

## 6. Lint contract

- [x] 6.1 Replace `phpcs.xml.dist` with PSR-12 base + targeted WP sniffs + Plugin Check, single `cs:fix` pass on `src/`
- [x] 6.2 Run full gate green (`cs:check`, `stan`, unit suite) as change evidence — `composer quality` green 2026-09-18 (73 tests, 181 assertions)
