## Why

SMO-121 is the data-layer half of the menu epic (SMO-73) and the critical path for the cycle: Subha's SMO-120 editor UI, the Bruno menus contracts, and downstream SMO-105/89 are all blocked on it. Today the menu surface is shells only — no tables, no repositories, no routes, no capability — so Gutenberg menu authoring has nothing to bind to and the API has no documented schema to validate against.

## What Changes

Split into two PRs so the SMO-120 handoff lands first:

**PR1 — contract + storage + capability:**
- Binding attribute contract published first (`docs/menu-binding-contract.md`: source name, attribute names, types, source args, example markup).
- One migration version (`0.2.0`) creating `smooth_menus`, `smooth_menu_items`, `smooth_modifiers` (composite keys on hot paths, no defaults under UNIQUE, no zero-dates).
- Three repositories behind new `Contracts/` interfaces, bound in `MenuProvider::register()`; pure helpers in `MenuService`.
- `smooth_manage_menus` defined via `map_meta_cap` → `manage_options` (no DB writes, stub-testable).
- Test-harness extensions (`FakeWpdb` read/write surface, REST/blocks/cache/capability stubs) + boot-matrix test updates for the bindings owner (below).

**PR2 — routes + bindings + cache + docs:**
- `MenuRoutes` controller in `Domains/Menu/`, called from `RestProvider::registerRoutes()` (RestProvider stays thin; public capability accessor added).
- Public paginated `GET /smooth/v1/menus` (+ `<id>`) with full JSON-Schema arrays and `Cache-Control`; management writes cap-gated.
- Read-only bindings source `smooth/menu` (`get_value_callback`, live table reads) registered on `init` in **BlocksProvider** (editor seam lives with the editor-aware provider); editor writes flow through the cap-gated management routes (SMO-120 wires them).
- Derived HTML/JSON memoized per request in `MenuService` (pure, testable); persistent cache + stampede guard deferred to a follow-up.
- Same-PR docs outputs: `api-docs/openapi.json` menus entries, Bruno `api-docs/smooth-v1/menus/` requests + `auth` negatives, HOOKS.md rows, revision-caveat note.

## Capabilities

### New Capabilities

- `menu-bindings-rest`: menu table schemas + repositories, capability model, paginated public REST reads with documented schemas + cap-gated writes, read-only live bindings in BlocksProvider, per-request derived memoization, and the SMO-120 binding contract.

### Modified Capabilities

- None. Existing specs gain menu coverage without requirement changes.

## Impact

- PR1: contract doc, migration `0.2.0`, 3 repos + 3 interfaces, `map_meta_cap` filter, stub/matrix test updates.
- PR2: `MenuRoutes`, bindings source, memoization, `openapi.json` + Bruno + HOOKS.md + caveat note.
- Money-path boundary: checkout totals read live `smooth_menu_items` prices at calculation time (no snapshots in M1) — no ledger/webhook touchpoints, not a money-path change.
- Tests: unit-verifiable via extended stubs; status-code mapping stays WP-core behavior (callbacks tested as booleans); no-postmeta/register-purity/domain-purity scans stay green.
