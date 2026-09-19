## 1. Contract-first publication (commit 1 — unblocks SMO-120)

- [x] 1.1 Add item/modifier paths, schemas, and error codes to `api-docs/openapi.json`
- [x] 1.2 Add Bruno item/modifier requests (happy paths + unauthenticated-write rejection, `auth-local` tags) under `api-docs/smooth-v1/menus/`
- [x] 1.3 Publish `docs/menu-write-contract.md` (URLs, fields, codes, examples) as the SMO-120 handoff
- [ ] 1.4 Commit the contract; notify SMO-120 that stubs are stable

## 2. Repository layer + migration 0.3.0

- [x] 2.1 Add `status` (`varchar(32)`, default `publish`) to `ModifierRepository::columnDefinitions()`
- [x] 2.2 Add optional `string $status = 'publish'` filter to `ModifierRepositoryInterface::listByItem()` and its implementation
- [x] 2.3 Add additive `maxSortOrder()` helpers to the item and modifier repository contracts + implementations
- [x] 2.4 Register the `0.3.0` closure (re-run all three menu `createTable()` calls) and bump `TARGET_VERSION` to `0.3.0`
- [x] 2.5 Extend `MenuMigrationTest` for the `status` backfill, idempotent re-run, and multisite version stamp

## 3. Domain events

- [x] 3.1 Add `MENU_ITEM_SAVED` / `MODIFIER_SAVED` constants to `DomainEvents`
- [x] 3.2 Add both rows (names, constants, payloads) to `HOOKS.md`

## 4. Controllers

- [x] 4.1 Extract shared params/respond/error/ownership plumbing for `Domains/Menu/` controllers
- [x] 4.2 Implement `MenuItemRoutes` (create, PATCH, delete with cascade, bulk order, schemas, events)
- [x] 4.3 Implement `ModifierRoutes` (create, PATCH, delete, bulk order, schemas, events)
- [x] 4.4 Filter the public `GET /menus/<id>` tree to publish items/modifiers (keep both-status cascade on delete)
- [x] 4.5 Delegate both controllers from `RestProvider::registerRoutes()`

## 5. Tests

- [x] 5.1 `MenuItemRoutesTest`: registration, permission booleans, validation, ownership, reorder strictness, cascade, events, schema loop
- [x] 5.2 `ModifierRoutesTest`: same coverage for the modifier surface
- [x] 5.3 Extend `FakeWpdb` parser only if `SELECT MAX(...)` needs it (follow the `1 === preg_match` convention)

## 6. Verification + closeout

- [x] 6.1 `composer quality` green (cs, PHPStan L8, unit suite)
- [ ] 6.2 Bruno write-path run against the Local site (app-password env)
- [x] 6.3 Update SMO-144 with evidence; update SMO-145 description `0.3.0` → `0.4.0`
