# provider-architecture Specification

## Purpose
TBD - created by archiving change core-provider-architecture. Update Purpose after archive.
## Requirements
### Requirement: Provider register/boot split

Each service provider SHALL bind services in `register()` with no side effects (no `add_action`, no DB access, no translation calls) and SHALL register hooks only in `boot()` with a context-gated early bail on the first line.

#### Scenario: Register stays pure

- **WHEN** `Plugin::boot()` runs `register()` for all providers on a frontend request
- **THEN** no hooks are added, no queries run, and no assets are enqueued until `boot()` executes

#### Scenario: Boot bails outside its context

- **WHEN** an admin-only provider boots on a diner frontend request
- **THEN** it returns before adding any hook or enqueue

### Requirement: Container contract

The container SHALL separate singleton instances from provider objects, SHALL expose `has()` and `instance()`, SHALL resolve explicit closure/singleton bindings without reflection on hot paths, and SHALL keep auto-wiring as a boot-time fallback only.

#### Scenario: Singleton resolved once

- **WHEN** a hot service is requested twice in one request
- **THEN** the identical instance is returned both times with no second reflection

#### Scenario: Test override

- **WHEN** a test pre-registers an instance via `instance()`
- **THEN** subsequent `make()` calls return the test double

### Requirement: Free/Pro provider seam

`Plugin::registerProviders()` SHALL build the Free provider list, pass it through the `smooth_restaurant_service_providers` filter with the container, validate each entry (class exists, extends `ServiceProvider`, meets version floor), register valid entries, and ignore + log invalid ones. Removal or replacement of Free providers via the filter SHALL be ignored. Pro providers SHALL be additive only.

#### Scenario: Pro adds a provider

- **WHEN** Pro hooks the filter and appends a valid provider
- **THEN** the provider registers and boots alongside Free providers

#### Scenario: Invalid entry rejected

- **WHEN** the filter yields a missing class or a version below the floor
- **THEN** it is skipped, an error is logged, and boot continues

### Requirement: Context-gated boot matrix

Providers SHALL boot per request context: diner menu boots Core, Database (read), Assets gate, Menu, Blocks (view); checkout POST adds Cart, Checkout, Orders, Payments, Slots, Notifications; admin boots Admin; REST boots Rest; cron boots the Notifications worker. Non-Smooth frontend requests SHALL enqueue zero Smooth assets.

#### Scenario: Non-Smooth page stays clean

- **WHEN** a public page without Smooth content renders
- **THEN** zero Smooth scripts or styles are enqueued

#### Scenario: Checkout boots the money path

- **WHEN** a checkout POST arrives
- **THEN** Cart, Checkout/Totals, Orders, Payments, Slots, and Notifications providers are booted exactly once

### Requirement: Migration runner contract

A `MigrationRunner` SHALL own the canonical `smooth_db_version` option, run version-guarded idempotent additive migrations in order, loop all sites on network-wide activation, and never run destructive renames in minors or backfills in the request path. This change ships the runner contract plus the multisite loop; real domain tables land in follow-up issues.

#### Scenario: Rerun is safe

- **WHEN** migrations run twice at the same version
- **THEN** the second run is a no-op and stored version is unchanged

#### Scenario: Network activation covers all sites

- **WHEN** the plugin is network-activated
- **THEN** every site receives pending migrations and its own `smooth_db_version`

### Requirement: Asset gate provider

An Assets provider SHALL own `smooth_should_load()`, read `.asset.php` dependency manifests from the `@wordpress/scripts` build, and enqueue per-surface assets only when the gate passes.

#### Scenario: Gate blocks off-Smooth enqueue

- **WHEN** `smooth_should_load()` returns false for the current request
- **THEN** no Smooth bundle is registered or enqueued

### Requirement: Compatibility floor

The plugin SHALL declare and test WordPress 6.8+ and PHP 8.2+ on every surface (plugin headers, `composer.json`, PHPCS `testVersion`, wp-env matrix, `readme.txt`, CI matrix). New code in `src/` MAY use PHP 8.2 idioms.

#### Scenario: Floor is consistent

- **WHEN** a reviewer audits headers, composer, lint config, wp-env, readme, and CI matrix
- **THEN** every surface states WP 6.8+ / PHP 8.2+ with no 6.4/8.1 remnant

#### Scenario: 8.2 idioms pass the gate

- **WHEN** new code uses readonly classes or enums
- **THEN** PHPCS and PHPStan pass on the PHP 8.2 configuration

