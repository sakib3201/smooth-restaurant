## Context

`MenuRoutes` (`src/Domains/Menu/MenuRoutes.php`, 486 lines) serves menu-row CRUD under `smooth/v1` with `MenuItemRepository` / `ModifierRepository` already constructed but used only for reads and cascade delete. The `smooth_modifiers` table has no `status` column; `MenuService` is pure; writes dispatch only `MENU_SAVED`. SMO-121's contract-first pattern (`docs/menu-binding-contract.md` as commit 0.1) is the precedent. Owner: Sakib (SMO-144, In Progress); consumer: Subha (SMO-120, blocked by the contract commit). Constraints: provider register-binds-only / boot-hooks-only, `$wpdb` only in `src/Database/`, prepared queries, cents-only money, no postmeta, LF endings, PHP 8.2-compatible code, `composer quality` green.

## Goals / Non-Goals

**Goals:**
- Give SMO-120 a stable, documented write path for items and modifiers (create, PATCH, delete, reorder) with machine-readable errors.
- Keep one migration version for cycle 3 (`0.3.0`) shared with SMO-89 if it needs schema.
- Fix the public-tree draft leak on the REST side without touching editor rendering semantics.

**Non-Goals:**
- Availability windows, allergen tags, modifier types/groups (SMO-89 owns modifier-state semantics beyond `status`).
- Editor UI (SMO-120), wp-admin CRUD screen (SMO-143), persistent cache (SMO-105), orders/Stripe, CSV/import, moving items between menus, binding write-through (stays read-only), `image_id` media validation beyond int ≥ 0.

## Decisions

**D1 — Nested route topology.** `POST /menus/{menu_id}/items`, `PATCH|DELETE /items/{id}`, `PUT /menus/{menu_id}/items/order`, `POST /items/{item_id}/modifiers`, `PATCH|DELETE /modifiers/{id}`, `PUT /items/{item_id}/modifiers/order`. Alternative (flat `/menu-items`) rejected: nesting maps 1:1 to the editor's `smooth/menuId` + `smooth/itemId` context and makes parent-ownership checks natural. New placeholders use `menu_id`/`item_id`; the existing `id` placeholder stays for single-resource routes.

**D2 — Modifier `status` column.** Add `status varchar(32) DEFAULT 'publish'` to `smooth_modifiers` (mirrors items). Alternative (delete-only modifiers) rejected per interview: the editor needs to stage/disable single add-ons, and item-level draft cannot express "item live, add-on paused". SMO-89 may later add richer modifier state on top.

**D3 — One migration for cycle 3.** `0.3.0` closure re-runs `createTable()` for all three menu tables (dbDelta converges current schema; idempotent), bumps `TARGET_VERSION` to `0.3.0`. Alternative (micro-versions per stream) rejected: version soup for no benefit while cycles run sequentially. Consequence: SMO-145's spec moves `0.3.0` → `0.4.0`; serialize-bumps rule goes to `agent_rules`.

**D4 — Bulk reorder as the canonical drag path.** Single `PUT …/order` with ordered `ids`; server assigns dense `0..n-1` via looped `update()`; strict set-equality (unknown/missing ids → `400` mismatch code, parent missing → `404`). Alternatives: per-row PATCH (N requests, client-owned invariant — rejected as primary) and sparse indexes (collision-prone, pushes invariant into JS — rejected). PATCH still accepts `sort_order` as a harmless escape hatch (id-ASC tiebreak keeps ordering deterministic). No transaction support: reorder is display-only, strict validation makes partial failure detectable, editor refetches and retries.

**D5 — Server-owned append.** Create without `sort_order` assigns `MAX(sort_order)+1` within the parent (new additive repo helpers `maxSortOrder()`), so appends land at the end without client math.

**D6 — REST-only draft fix.** Public `GET /menus/<id>` filters to publish items/modifiers; delete cascade still covers both statuses. The `smooth/menu` binding's editor-context discrimination (unreliable: `is_admin()` is false in editor REST renders) is explicitly deferred to SMO-105/120 scope.

**D7 — Two controllers + extracted plumbing.** New `MenuItemRoutes` + `ModifierRoutes` in `Domains/Menu/`; shared params/respond/error/ownership helpers extracted once (trait vs. tiny final class left to code review); `RestProvider::registerRoutes()` gains two delegations. Alternative (grow `MenuRoutes` past 900 lines) rejected for ownership and test-file clarity.

**D8 — Events: granular + parent.** New `MENU_ITEM_SAVED` (`smooth_restaurant_menu_item_saved`) and `MODIFIER_SAVED` with `{action: created|updated|deleted|reordered, id, menu_id, item_id?, row?}` payloads, plus parent `MENU_SAVED` re-fired with `{id: menu_id, reason: 'item'|'modifier', action}` as the tree-changed signal for future cache invalidation. Payloads always carry `menu_id` so subscribers invalidate by scope. HOOKS.md gains two rows.

**D9 — Contract-first commit.** Commit 1 = `api-docs/openapi.json` paths + Bruno requests + new `docs/menu-write-contract.md` (URLs, fields, codes, examples). A new doc (not an edit of `menu-binding-contract.md`, which is SMO-121-owned and changes go through SMO-120 first).

**D10 — `listByItem()` status filter as optional param.** `listByItem(int $itemId, string $status = 'publish')` — backward-compatible for existing callers; covered by SMO-144 as the Linear issue + this spec per the Contracts stability rule.

## Risks / Trade-offs

- [Risk] SMO-89 also needs cycle-3 schema → two authors, one `0.3.0` closure → Mitigation: SMO-144 owns the closure; SMO-89 amends it before either merges; serialize rule written to `agent_rules`.
- [Risk] SMO-145 spec still claims `0.3.0` → Mitigation: edit SMO-145 to `0.4.0` before cycle 4 starts.
- [Risk] Reorder without transactions leaves partial order on failure → Mitigation: strict set-equality + mismatch code; editor refetch/retry; display-only data.
- [Risk] Public GET behavior change (drafts disappear) → Mitigation: pre-1.0 bug fix; Bruno smoke asserts publish-only tree; spec updated.
- [Risk] Bruno writes are app-password-gated, excluded from credential-free CI → Mitigation: `auth-local` tags, local verification documented in the issue; unit tests carry CI proof.
- [Risk] `FakeWpdb` may not parse `SELECT MAX(...)` → Mitigation: implement via existing `fetchRow` + `prepare`; extend the test-double parser following its `1 === preg_match` convention if needed.
- [Risk] `permission_callback` receives the request object into `current_user_can()` extras → Mitigation: unchanged existing pattern; `manage_options` mapping ignores extras (covered by `MenuCapabilityTest` style tests).

## Migration Plan

1. Land `0.3.0` closure + `TARGET_VERSION` bump with the implementation commits (never the contract commit — contract is docs/API-shape only).
2. Deploy = normal plugin update; `migrateAll()` applies idempotently, multisite included; existing modifier rows backfill to `publish`.
3. Rollback: additive column only; code rollback leaves an unused nullable-equivalent column (default `publish`) — no down-migration by policy.

## Open Questions

- Does SMO-120 ever reorder a filtered view (breaks strict set-equality)?
- Does M1 need draft-modifier preview-by-link, or is filtering sufficient?
- Shared plumbing shape: trait vs. tiny final static-helper class?
- `image_id`: int-only, or `wp_attachment_is_image()` check in production?
