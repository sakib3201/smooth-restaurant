# Menu Binding Contract — SMO-121 → SMO-120

Source of truth for the Gutenberg bindings SMO-120 builds against. Owned by
SMO-121 (`menu-bindings-rest`); changes after publication go through SMO-120
first.

## Source

- Name: `smooth/menu`
- Registered on `init` by `BlocksProvider::registerBlocks()` via
  `register_block_bindings_source()` with `label`, `get_value_callback`, and
  `use_context`. Read-only: no `set_value_callback`.
- Reads live custom-table rows on every render; never touches revision data,
  so autosave cannot corrupt menu output.

## Context

Blocks declare the ids they render through block context:

| Key               | Type | Required | Meaning                        |
| ----------------- | ---- | -------- | ------------------------------ |
| `smooth/menuId`   | int  | yes      | Row id in `smooth_menus`.      |
| `smooth/itemId`   | int  | no       | Row id in `smooth_menu_items`. |

## Keys (`args.key`)

| Key                  | Returns        | Source                                     |
| -------------------- | -------------- | ------------------------------------------ |
| `menu/name`          | string \| null | `smooth_menus.name`                        |
| `menu/description`   | string \| null | `smooth_menus.description`                 |
| `item/name`          | string \| null | `smooth_menu_items.name`                   |
| `item/description`   | string \| null | `smooth_menu_items.description`            |
| `item/price`         | string \| null | `smooth_menu_items.price_cents`, formatted |
| `item/price_raw`     | int \| null    | `smooth_menu_items.price_cents` (cents)    |
| `modifier/name`      | string \| null | `smooth_modifiers.name` (see note)         |
| `modifier/price`     | string \| null | `smooth_modifiers.price_cents`, formatted  |

`modifier/*` keys read the first modifier row for the context item in M1
(`use_context` carries no modifier id yet); multi-modifier selection is
SMO-89 scope.

Unknown keys and missing rows return `null` (block falls back to its static
content). Money formatting is display-only; checkout totals always read live
`price_cents` at calculation time (no snapshots in M1).

## Example markup

```html
<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"smooth/menu","args":{"key":"item/name"}}}}} -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"context":{"smooth/menuId":12,"smooth/itemId":34},"metadata":{"bindings":{"content":{"source":"smooth/menu","args":{"key":"item/price"}}}}} -->
<p></p>
<!-- /wp:paragraph -->
```

## Writes

The source is read-only. Editor writes (rename item, change price, reorder)
travel through the cap-gated management REST routes (`smooth_manage_menus`),
which SMO-120 calls; the source reflects the new table state on next render.

## Revision caveat

Menu data lives in custom tables, not post content. Restoring a post
revision does **not** roll back menu table rows — the revision restores the
block markup (bindings + context ids), while names/prices keep showing the
current table state. This is by design (single source of truth for checkout);
revision snapshots of menu data are a separate issue if ever wanted.
