## Why

SMO-120 (menu block editing UI) needs a documented write path for item and modifier rows: today only menu rows have REST endpoints, and the `smooth/menu` binding source is read-only by design. Without capability-gated item/modifier writes plus reorder, the editor cannot persist authoring. Contract-first: the endpoint contract ships as the first commit so SMO-120 starts in parallel — the same handoff pattern as SMO-121 → SMO-120. (Linear SMO-144, In Progress, cycle 3 menu end-to-end.)

## What Changes

- New cap-gated REST writes under `smooth/v1` for items and modifiers: create, partial update (PATCH), delete (item delete cascades its modifiers), and bulk reorder endpoints for drag/reorder + autosave.
- Item fields: menu linkage, `name`, `description`, `price_cents`, `image_id` (WP media reference only), `status`, `sort_order`. Modifier fields: item linkage, `name`, `price_cents`, `status` (new column), `sort_order`.
- Migration `0.3.0`: additive `status` column on `smooth_modifiers` via dbDelta re-run. This change owns cycle 3's single version bump (SMO-89 folds into the same version if it needs schema; SMO-145 moves to `0.4.0`).
- Public `GET /menus/<id>` tree excludes draft items and modifiers (draft-leak fix, REST side only; the binding/editor-context side stays out of scope for SMO-105/120). Pre-1.0 behavior fix, not a versioned breaking change.
- JSON Schemas for item/modifier objects plus route-arg validation; machine-readable error codes (`smooth_menu_item_not_found`, `smooth_menu_item_missing_name`, `smooth_modifier_not_found`, `smooth_modifier_missing_name`, order-mismatch code).
- Domain events: new `MENU_ITEM_SAVED` / `MODIFIER_SAVED` (past tense, `action` + `menu_id` in payload) plus the parent `MENU_SAVED` re-fired as the tree-changed signal; HOOKS.md rows added.
- Unit tests for callbacks, validation, ownership, reorder, events, and schema coverage; Bruno happy-path requests (auth-local, verified locally — CI stays credential-free).
- No new tables; no postmeta; repository-only access; money stays in cents.

## Capabilities

### New Capabilities

- `item-modifier-rest`: item/modifier REST write endpoints (create, PATCH, delete with cascade, bulk reorder), JSON schemas and error codes, write events, the modifier `status` column with migration `0.3.0`, and publish-only public tree filtering.

### Modified Capabilities

- `menu-bindings-rest`: the public single-menu tree now excludes draft rows; the management-route surface extends to item/modifier writes, fulfilling the spec's "editor writes SHALL travel through the cap-gated management routes" requirement; `MenuRoutes` gains sibling controllers.

## Impact

- `src/Domains/Menu/` (new `MenuItemRoutes` + `ModifierRoutes`, shared plumbing, `MenuRoutes` tree filter), `src/Contracts/` + `src/Database/Repositories/` (status-filtered modifier reads, MAX-sort helper), `src/Database/MigrationRunner.php` (`0.3.0`), `src/Domains/Shared/DomainEvents.php` (two constants), `src/Providers/RestProvider.php` (delegation), `api-docs/openapi.json` + Bruno requests, a short write-contract doc, `HOOKS.md`, tests.
- Unblocks SMO-120; constrains SMO-89 (shares `0.3.0`) and SMO-145 (renumber to `0.4.0`).
