## Context

The rebuild branch boots from `smooth-restaurant.php` (constants + autoload + activation hooks + `Plugin::instance()->boot()` on `plugins_loaded`) into an empty `registerProviders()`. The `Container` auto-wires via reflection and conflates singleton instances with provider objects (`$providers` keyed + appended). `Activator`/`Deactivator` only flush rewrites; migrations, roles, and `smooth_db_version` are SMO-72 follow-ups. `sm_docs/` is source of truth: standalone-native, custom tables, `smooth_service_providers` Free/Pro seam, Gutenberg-first, `smooth_should_load()` gate with per-surface budgets, M1 money-path scope, and `16 §13` engineering standards. Build is locked to `@wordpress/scripts` (revised 2026-09-18). Solo founder at 10–15 h/wk with CLI agents; tests are the merge contract.

## Goals / Non-Goals

**Goals:**
- One provider per M1 bounded context with a strict `register()` (bind-only, no hooks/DB/i18n) vs `boot()` (hooks with context-gated early bail) split.
- Container fixed for clarity and hot-path safety: separate instance/provider stores, `has()`/`instance()`, reflection at boot only.
- Free/Pro seam that is additive-only and review-enforceable, with version-floor check.
- Lint contract matching `16 §13.1` so CI enforces the hybrid instead of fighting it.
- Compatibility floor swept to WP 6.8+ / PHP 8.2+ in one pass; new code may use PHP 8.2 idioms (readonly, enums).
- One table-access pattern (`BaseRepository` + dbDelta-first) with a failing-if-violated no-postmeta guard.

**Non-Goals:**
- No M1 domain logic (Totals, ledger, gateways, slots) — architecture only, domains land as follow-up issues.
- No migration implementation beyond the runner contract and `smooth_db_version` ownership.
- No asset pipeline changes beyond the provider's `.asset.php` + gate role.
- No Pro code; only the Free-side seam Pro will consume.

## Decisions

- **Modular monolith over micro-packages or includes-soup.** One `src/Providers/*Provider` per domain (`Menu`, `Cart`, `Checkout`, `Orders`, `Payments`, `Slots`, `Reservations`, `Tables`, `Notifications`, `Admin`, `Rest`, `Blocks`) plus `Core`, `Database`, `Assets` providers. Alternative (single `AppProvider`) rejected: hides load cost and merge conflicts on a provider-per-issue workflow. Alternative (per-domain Composer packages) rejected: overhead a solo maintainer cannot carry.
- **Keep the hand-rolled container, fix naming and surface.** Split `$instances` from `$providers`, add `has()`/`instance()`, keep auto-wiring as boot-time fallback with explicit closure/singleton bindings for hot services. Alternative (Illuminate/PSR-11 package) rejected: dependency weight + WP Plugin Check friction for no M1 need.
- **Context-gated boot matrix, not deferred lazy-loading framework.** Each `boot()` bails first (`is_admin()`, `REST_REQUEST`, `wp_doing_cron()`, `smooth_should_load()`). Full `when()`/`provides()` deferred system deferred as over-engineering; simple guards survive agent authorship. Diner menu boots Core+DB(read)+Assets(gate)+Menu+Blocks(view); checkout POST adds Cart/Checkout/Orders/Payments/Slots/Notify; admin adds Admin; REST adds Rest; cron adds Notify worker.
- **Pro seam as filtered provider list.** `Plugin::registerProviders()` builds the Free list, passes through `apply_filters('smooth_service_providers', $list, $container)`, validates class + interface + version floor, then registers. Contracts (`GatewayInterface`, slot/notifier interfaces, provider extension shape) live in Free `src/Contracts/` forever. Alternative (Pro subclasses Free providers) rejected: fragile coupling, breaks lockstep SemVer.
- **Pure domain cores with WP adapters at the edge.** `Totals::calculate()`, money/time/idempotency helpers take arrays and return arrays; providers inject `$wpdb`/options only into repositories/gateways. Enables golden fixtures and keeps reflection/IO out of hot paths.
- **PSR-12 base + targeted WP sniffs.** Base ruleset PSR-12 (strict types, signatures, modern OO) plus WP hooks/naming/capability/nonce/i18n sniffs + Plugin Check in CI. Current pure-WordPress ruleset with ~10 disables is inverted and will be replaced. WPCS-only rejected: fights `declare(strict_types=1)` typed OO the docs mandate.
- **Full floor sweep, not headers-only.** Plugin headers, `composer.json`, PHPCS `testVersion`, wp-env matrix, `readme.txt`, and CI matrix move to WP 6.8+ / PHP 8.2+ together so no surface lies about support. New code may use 8.2 idioms; conservative-8.1 style rejected as it wastes the floor SMO-72 acceptance requires.
- **Shared `BaseRepository`, dbDelta-first.** One thin base over `$wpdb` owns prefix, charset/collate, `prepare()`, and typed-row mapping; domains implement per-table repos and never touch raw `$wpdb` outside. Schema uses `dbDelta` for creates, raw SQL only for indexes/keys `dbDelta` cannot express. Direct-`$wpdb`-everywhere rejected: duplicates prefix/prepare discipline across 12+ providers on a solo budget.
- **Pro reads Free, writes its own.** Pro consumes Free repository interfaces for reads and Free services/ledger APIs for writes; direct Free-table writes are out. Keeps the append-only ledger and lockstep SemVer intact.
- **No-postmeta guard as architecture test.** PHPUnit scans `Domains/` + `Database/` for postmeta/`meta_query` usage on transactional entities and fails the PR; the optional read-only menu CPT mirror (sm_docs D4) is the sole exception. PHPCS-sniff and grep-gate alternatives rejected: the arch test reads as a spec and runs with the unit suite agents already own.

## Risks / Trade-offs

- [Risk] Provider sprawl (12+ providers) confuses agents → Mitigation: one-folder-per-domain mirror (`Providers/XProvider` ↔ `Domains/X/`), boot matrix table in spec, reviewer checks `register()` purity.
- [Risk] Reflection on hot path regresses p95 order-create → Mitigation: closure/singleton bindings for Totals/slots/rules, per-request memoization, PHPCS/PHPStan + perf budget gate.
- [Risk] Pro filter abused for mutation/removal of Free providers → Mitigation: validation (instanceof + version floor), additive-only documented, removal attempts ignored + logged.
- [Risk] Lint switch churns existing files → Mitigation: single `cs:fix` pass scoped to `src/`, then gate; rebuild branch has minimal code so blast radius is tiny.
- [Risk] Migration runner scope creep (backfills in request path) → Mitigation: contract mandates version-guarded idempotent additive migrations, background backfills only.
- [Risk] Floor sweep misses a surface (header says 6.8, matrix still tests 6.4) → Mitigation: single task lists every surface; smoke boots wp-env on the new floor.
- [Risk] `BaseRepository` becomes a god class → Mitigation: base owns only prefix/prepare/hydration; query logic lives in per-table repos.
