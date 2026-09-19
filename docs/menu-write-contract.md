# Menu Write Contract — SMO-144 → SMO-120

Source of truth for the item/modifier REST writes SMO-120 builds against.
Owned by SMO-144 (`item-modifier-rest`); contract changes after publication
need both streams. Shapes mirror `api-docs/openapi.json` (authoritative for
field types) and the Bruno requests in `api-docs/smooth-v1/menus/`
(`07`–`15`).

## Base + auth

- Base: `{site}/wp-json/smooth/v1`.
- All writes require `smooth_manage_menus` (mapped to `manage_options`):
  `401` without credentials, `403` without the cap. Public reads stay
  capability-free.

## Endpoints

| Method | URL | Success | Meaning |
| ------ | --- | ------- | ------- |
| `POST` | `/menus/{menu_id}/items` | `201` | Create an item under a menu |
| `PATCH` | `/items/{id}` | `200` | Partial item update (present fields only) |
| `DELETE` | `/items/{id}` | `200` | Delete an item; its modifiers cascade |
| `PUT` | `/menus/{menu_id}/items/order` | `200` | Bulk reorder a menu's items |
| `POST` | `/items/{item_id}/modifiers` | `201` | Create a modifier under an item |
| `PATCH` | `/modifiers/{id}` | `200` | Partial modifier update (present fields only) |
| `DELETE` | `/modifiers/{id}` | `200` | Delete a modifier |
| `PUT` | `/items/{item_id}/modifiers/order` | `200` | Bulk reorder an item's modifiers |

Reads stay where they were: `GET /menus` (paginated collection) and
`GET /menus/<id>` (tree). There is no item/modifier list endpoint — reads
travel through the tree.

## Fields

Item (`menu_id` comes from the URL and is immutable):

| Field | Type | Default | Rules |
| ----- | ---- | ------- | ----- |
| `name` | string | — | Required on create, must not be empty |
| `description` | string | `""` | Free text |
| `price_cents` | integer | `0` | Must be `>= 0` |
| `image_id` | integer | `0` | WP media attachment id, `>= 0`, int-only (no `wp_attachment_is_image()` check) |
| `status` | string | `"publish"` | `publish` or `draft` |
| `sort_order` | integer | server-assigned | Omitted on create appends (`MAX(sort_order)+1`); PATCH accepts it as an escape hatch |

Modifier (`item_id` comes from the URL and is immutable):

| Field | Type | Default | Rules |
| ----- | ---- | ------- | ----- |
| `name` | string | — | Required on create, must not be empty |
| `price_cents` | integer | `0` | Must be `>= 0` |
| `status` | string | `"publish"` | `publish` or `draft` — stages single add-ons without touching the item |
| `sort_order` | integer | server-assigned | Same append/reorder semantics as items |

## Reorder

- Body: `{"ids": [...]}` — the parent's **full** ordered id set.
- The server assigns dense `sort_order` (`0..n-1`) and echoes
  `{"data": {"ids": [...]}}`.
- Strict set-equality: unknown or missing ids → `400` with the mismatch
  code and **no** `sort_order` changes. Always send the full set, never a
  filtered view — refetch the tree after a rejection and retry.
- Reorder is display-only; there is no transaction wrapping (strict
  validation makes partial failure detectable).

## Error codes

| Code | Status | When |
| ---- | ------ | ---- |
| `smooth_menu_not_found` | `404` | Unknown menu (create/reorder parent) |
| `smooth_menu_item_not_found` | `404` | Unknown item (update/delete/reorder/modifier parent) |
| `smooth_modifier_not_found` | `404` | Unknown modifier, or a modifier addressed under an item it does not belong to |
| `smooth_menu_item_missing_name` | `400` | Missing/empty item name |
| `smooth_modifier_missing_name` | `400` | Missing/empty modifier name |
| `smooth_menu_item_invalid_price` | `400` | Non-integer or negative item `price_cents` |
| `smooth_menu_item_invalid_image` | `400` | Non-integer or negative item `image_id` |
| `smooth_modifier_invalid_price` | `400` | Non-integer or negative modifier `price_cents` |
| `smooth_menu_item_order_mismatch` | `400` | Item order id set is not the parent's full set |
| `smooth_modifier_order_mismatch` | `400` | Modifier order id set is not the parent's full set |

Every error body is `{"code", "message", "data": {"status"}}`.

## Examples

Create a draft item (appends at the end):

```json
POST /menus/1/items
{"name": "Soup", "description": "Hot soup", "price_cents": 950, "status": "draft"}
// 201 {"data": {"id": 7, "menu_id": 1, "name": "Soup", "status": "draft", "sort_order": 3}}
```

Reorder after drag:

```json
PUT /menus/1/items/order
{"ids": [9, 7, 8]}
// 200 {"data": {"ids": [9, 7, 8]}}
```

Delete an item (modifiers cascade, both statuses):

```json
DELETE /items/7
// 200 {"data": {"deleted": true, "id": 7}}
```

## Events (for SMO-105 cache work)

Every write dispatches a granular event plus the parent tree-changed
signal — payloads always carry `menu_id`:

- `smooth_restaurant_menu_item_saved` (`DomainEvents::MENU_ITEM_SAVED`):
  `{action: created|updated|deleted|reordered, id, menu_id, row?}`.
- `smooth_restaurant_modifier_saved` (`DomainEvents::MODIFIER_SAVED`):
  `{action, id, menu_id, item_id, row?}`.
- `smooth_restaurant_menu_saved` re-fired with
  `{id: menu_id, reason: 'item'|'modifier', action}`.

## Known limitations

- The public tree (`GET /menus/<id>`) returns **publish rows only**;
  draft items/modifiers are invisible publicly (pre-1.0 fix, SMO-144).
  Delete cascades still cover both statuses.
- Editor preview shows the same publish-only rows until SMO-105/120 adds
  editor-context discrimination — draft add-ons have no preview-by-link
  in M1.
- Moving an item between menus is not supported (`menu_id` immutable);
  recreate the row instead.

## Notes / Changelog

- 2026-09-19: published alongside SMO-144 (migration `0.3.0`, shared with
  SMO-89 if it needs schema; SMO-145 moves to `0.4.0`).
