## 0. Handoff first

- [x] 0.1 Publish `docs/menu-binding-contract.md` as the first branch commit (source `smooth/menu`, attribute names, types, source args, example markup) and notify SMO-120

## PR1 — contract + storage + capability + harness

- [x] 1.1 Add migration `0.2.0` creating `smooth_menus`, `smooth_menu_items`, `smooth_modifiers` (composite keys on hot paths, no-default key columns, `CURRENT_TIMESTAMP`, dbDelta spacing) + bump `TARGET_VERSION` to `0.2.0`
- [x] 1.2 Create 3 `Contracts/` repository interfaces + 3 `Database/Repositories/` implementations (variable-interpolating queries via `prepare()` only) + bind singletons in `MenuProvider::register()`
- [x] 1.3 Define `smooth_manage_menus` via `map_meta_cap` → `manage_options` in `MenuProvider::boot()`; permission-callback unit tests via the `apply_filters` stub (allowed/denied booleans)
- [x] 1.4 Extend test harness: `FakeWpdb` read/write surface (`get_results`, `get_row`, `insert`, `update`, `delete`); `WpStubs` entries for `register_rest_route`, `register_block_bindings_source`, `wp_cache_*`, controllable `current_user_can`
- [x] 1.5 Repository unit tests via extended `FakeWpdb` (CRUD round-trip, key generation); full gate green (`composer quality`, purity + no-postmeta scans)

## PR2 — routes + bindings + memo + docs

- [x] 2.1 Create `MenuRoutes` controller in `Domains/Menu/` (public paginated list + single `GET` with full `schema` arrays incl. params/headers/bodies + `Cache-Control`; cap-gated management writes) + public `RestProvider::capability()` accessor + ~3-line delegation in `registerRoutes()`
- [x] 2.2 REST tests: public reads 200, callback booleans for unauthenticated/without-cap, schema-validation tests
- [x] 2.3 Register `smooth/menu` source on `init` in `BlocksProvider` (`label`, `get_value_callback` live reads, `use_context`); update `BootMatrixTest` + `ContextFilterTest` (+ `RestBootTest`) for expanded `MenuProvider` contexts (frontend+admin+rest for the cap map); binding round-trip tests (reorder, inline edit, autosave) + revision-caveat comment + editor notice if cheap
- [x] 2.4 Per-request derived memoization in `MenuService` (pure key templates, caller-supplied blog id); memoization unit tests
- [x] 2.5 Docs-PR outputs: `api-docs/openapi.json` menus entries + Bruno `api-docs/smooth-v1/menus/` requests + `auth` negatives + HOOKS.md rows (`smooth_restaurant_menu_saved` via `DomainEvents::MENU_SAVED`); revision-caveat user note
- [x] 2.6 Full gate green + `openspec validate menu-bindings-rest`; anything unverifiable without live WP gets a BLOCKED annotation, not a fake test
