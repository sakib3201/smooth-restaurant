# item-modifier-rest Specification

## Purpose
Item and modifier REST writes for the menu editor (SMO-144, archived 2026-09-19): capability-gated create, PATCH, delete with cascade, and bulk reorder under `smooth/v1`, with JSON schemas, machine-readable errors, write events, the modifier `status` column at migration `0.3.0`, and publish-only public trees.

## Requirements
### Requirement: Modifier status column at version 0.3.0

Migration `0.3.0` SHALL add a `status` column (`varchar(32) NOT NULL DEFAULT 'publish'`) to `smooth_modifiers` via an idempotent dbDelta re-run, backfill existing rows to `publish`, and bump `TARGET_VERSION` to `0.3.0`. The `0.3.0` closure SHALL re-run `createTable()` for all three menu tables so cycle-3 schema converges in a single version.

#### Scenario: Fresh migration adds the column

- **WHEN** the plugin migrates from `0.2.0` with existing modifier rows
- **THEN** every modifier row carries `status = 'publish'` and the stored version equals `0.3.0`, including on multisite via `migrateAll`

#### Scenario: Repeat migration is a no-op

- **WHEN** the `0.3.0` step retries after partial failure
- **THEN** tables and keys are intact and the stored version remains `0.3.0`

### Requirement: Item write endpoints

The system SHALL expose capability-gated item writes: `POST /menus/{menu_id}/items` (201), `PATCH /items/{id}` (200), `DELETE /items/{id}` (200 deletion receipt, cascading the item's modifiers). The parent menu MUST exist (`404 smooth_menu_not_found`); the item MUST exist for update/delete (`404 smooth_menu_item_not_found`); `name` MUST be non-empty on create (`400 smooth_menu_item_missing_name`); PATCH SHALL touch only present fields; `price_cents` and `image_id` SHALL be integers ≥ 0; `status` SHALL be `publish` or `draft` (default `publish`); omitted `sort_order` on create SHALL assign `MAX(sort_order)+1` within the menu; `menu_id` SHALL be immutable.

#### Scenario: Item create round-trip

- **WHEN** a capable client posts a valid item under an existing menu
- **THEN** the response is `201` with the row (including server-assigned `sort_order`) and a `MENU_ITEM_SAVED` event fires with `action = 'created'`

#### Scenario: Item validation rejects

- **WHEN** a capable client posts without a name, with a negative price, or under an unknown menu
- **THEN** the response carries `smooth_menu_item_missing_name` (`400`), a price error (`400`), or `smooth_menu_not_found` (`404`) respectively, and no row is written

#### Scenario: Item delete cascades

- **WHEN** a capable client deletes an item with modifiers
- **THEN** the response is `{"data": {"deleted": true, "id": <id>}}` and the item's modifiers no longer list

### Requirement: Modifier write endpoints

The system SHALL expose capability-gated modifier writes: `POST /items/{item_id}/modifiers` (201), `PATCH /modifiers/{id}` (200), `DELETE /modifiers/{id}` (200 deletion receipt). The parent item MUST exist (`404 smooth_menu_item_not_found`); the modifier MUST exist for update/delete (`404 smooth_modifier_not_found`); `name` MUST be non-empty on create (`400 smooth_modifier_missing_name`); `price_cents` SHALL be an integer ≥ 0; `status` SHALL be `publish` or `draft` (default `publish`); omitted `sort_order` on create SHALL assign `MAX(sort_order)+1` within the item; `item_id` SHALL be immutable.

#### Scenario: Modifier create round-trip

- **WHEN** a capable client posts a valid modifier under an existing item
- **THEN** the response is `201` with the row and a `MODIFIER_SAVED` event fires with `action = 'created'`

#### Scenario: Modifier ownership enforced

- **WHEN** a client addresses a modifier under an item it does not belong to, or an unknown modifier id
- **THEN** the response is `404 smooth_modifier_not_found` and no row is written

### Requirement: Bulk reorder endpoints

The system SHALL expose `PUT /menus/{menu_id}/items/order` and `PUT /items/{item_id}/modifiers/order` accepting an ordered `ids` array, assigning dense `sort_order` (`0..n-1`) server-side and echoing the ordered ids. The id set MUST equal the parent's full current set: unknown or missing ids SHALL yield `400` (`smooth_menu_item_order_mismatch` / `smooth_modifier_order_mismatch`); an unknown parent SHALL yield `404`.

#### Scenario: Reorder round-trip

- **WHEN** a capable client puts the full ordered id set for a menu's items
- **THEN** the response echoes the ids, subsequent reads return the new order, and a reorder event fires

#### Scenario: Stale order rejected

- **WHEN** a capable client puts an id set with unknown or missing ids
- **THEN** the response is `400` with the mismatch code and no `sort_order` changes

### Requirement: Publish-only public tree

`GET /menus/<id>` SHALL return only `publish` items and `publish` modifiers; draft rows SHALL be invisible publicly. Delete cascades SHALL still cover both statuses.

#### Scenario: Draft rows hidden publicly

- **WHEN** a menu has draft items or draft modifiers
- **THEN** the public tree omits them while management reads still see them

### Requirement: Item and modifier write events

Every item/modifier write SHALL dispatch `MENU_ITEM_SAVED` (`smooth_restaurant_menu_item_saved`) or `MODIFIER_SAVED` (`smooth_restaurant_modifier_saved`) with `{action, id, menu_id, item_id?, row?}` where action is `created`, `updated`, `deleted`, or `reordered`, and SHALL re-fire the parent `MENU_SAVED` with `{id: menu_id, reason: 'item'|'modifier', action}` as the tree-changed signal. HOOKS.md SHALL gain both rows.

#### Scenario: Create dispatches granular plus parent

- **WHEN** an item is created
- **THEN** subscribers observe `MENU_ITEM_SAVED` with `action = 'created'` and a parent `MENU_SAVED` with `reason = 'item'`

### Requirement: Contract-first publication

The change's first commit SHALL publish the endpoint contract — `api-docs/openapi.json` paths, Bruno requests, and `docs/menu-write-contract.md` (URLs, fields, codes, examples) — so SMO-120 builds against stubs.

#### Scenario: UI build handoff

- **WHEN** Subha starts SMO-120 against the published contract
- **THEN** no SMO-144 implementation detail beyond the contract is needed to call the endpoints

### Requirement: Write-path test coverage

Unit tests SHALL cover permission-callback booleans, validation errors, parent ownership, reorder strictness, delete cascades, event payloads, and response-vs-schema loops; Bruno SHALL cover the happy paths plus unauthenticated-write rejection as `auth-local` requests verified locally.

#### Scenario: Gates green

- **WHEN** `composer quality` runs with the change applied
- **THEN** codesniffer, PHPStan level 8, and the unit suite (including the new route tests) pass
