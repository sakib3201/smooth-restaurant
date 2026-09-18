## Context

SMO-121 (`In Progress`, assignee Sakib). All menu surfaces are shells: `MenuProvider` (frontend-only), `MenuService` (empty), `RestProvider::registerRoutes()` (empty, `requireCapability` protected), `BlocksProvider` (`['frontend','admin']`, the only editor-aware provider). Zero references to `smooth_manage_menus`, bindings APIs, or menu tables. Sibling SMO-120 (Subha) builds the editor UI against the binding contract defined here. Constraints: provider register/boot discipline + contexts() mirror rule (enforced by `BootMatrixTest`, `ContextFilterTest`, `ProviderLifecycleTest`), `$wpdb` confined to `src/Database/`, every variable-interpolating query through `BaseRepository::prepare()` (DDL/`dbDelta` excluded by nature), no-postmeta scan, LF endings, PHP 8.2. No Docker locally, billing-locked CI: live-WP verification rides on reviewers/CI when restored.

## Goals / Non-Goals

**Goals:**
- Transactional menu storage with repository access, reachable from blocks and REST.
- Public menu reads cheap via per-request memoization; management strictly cap-gated.
- A binding contract SMO-120 can build against from the first commit.
- Docs-PR outputs (schema, `openapi.json` seed, Bruno requests, HOOKS.md) in PR2.

**Non-Goals:**
- Editor UI blocks themselves (SMO-120); CPT mirror (deferred); CSV/AI import (out of M1); menu modifiers/availability UX (SMO-89); diner search/filters (SMO-105); persistent cache + stampede guard (follow-up); revision snapshots (own issue if ever wanted).

## Decisions

### 1. Two PRs, contract as task 0.1
PR1 (contract + migration + repos + cap + harness) unblocks Subha immediately; PR2 (routes + bindings + memo + docs) completes the issue. Rejected: single mega-PR (keeps the critical handoff hostage to the full review).

### 2. Migration `0.2.0`, three tables, idempotent retry
One version bump from `0.1.0` creating all three tables; each `dbDelta` call independently re-runnable so partial failure retries cleanly. Schemas + composite keys sketched in tasks. Rejected: per-table versions (triple review surface for co-shipped tables).

### 3. Repositories behind interfaces, bound in `MenuProvider::register()`
Three `Contracts/` interfaces, three `Database/Repositories/` implementations, singletons bound in `register()` (bind-only preserved). `MenuService` keeps pure tree assembly + in-memory per-request memoization (no WP calls — stays `DomainPurityTest`-clean). Cache keys are pure string templates; the calling layer supplies the blog id so multisite namespacing never depends on a global inside the domain.

### 4. Capability via `map_meta_cap` filter
`smooth_manage_menus` maps to `manage_options` through a `map_meta_cap` filter owned by `MenuProvider` (hooked in `boot()` for admin+rest contexts — exact contexts fixed in tasks with matrix updates). No role writes, multisite-correct (per-site `manage_options` semantics preserved), testable through the existing `apply_filters` stub. Rejected: role seeding (activation writes, multisite edges) and bare `manage_options` checks (loses the stable permission vocabulary).

### 5. `MenuRoutes` in `Domains/Menu/`, RestProvider delegates
Controller lives with the domain (no new top-level dir, no `folder-structure.md` amendment). `RestProvider::registerRoutes()` gains a ~3-line delegation; a public `capability(string $cap): callable` accessor wraps the protected `requireCapability`. Construction: controller resolved from the container with the three bound repositories — no `$wpdb` outside `src/Database/`. Rejected: inline registration (dumping ground) and `src/Rest/` (precedent-setting new root).

### 6. Read-only bindings source in BlocksProvider
`smooth/menu` source registered on `init` in `BlocksProvider::registerBlocks()`: `label`, `get_value_callback($source_args, $block_instance, $attribute_name)` reading live tables, `use_context` for menu/item ids. No `set_value_callback` — editor writes travel through the cap-gated management REST routes, which SMO-120 calls. Autosave-safe (reads never touch revision data); revision restores don't roll back tables — caveat in the contract doc + code comment + editor notice if cheap. Ownership rationale: editor seam lives with the editor-aware provider; `MenuProvider` stays frontend.

### 7. Per-request memoization now, persistent cache later
`MenuService` memoizes derived HTML/JSON in-memory per request (zero infrastructure, fully unit-testable). Persistent object cache + version keys + stampede guard become a follow-up once traffic warrants — the honest way to promise a guard, since default WP cache is per-request and locks would evaporate. Rejected: object-cache-now (unpromisable guard), transients (same eviction class, new failure modes), cache columns (schema churn per rendering change).

### 8. Public list is paginated + cached headers
`GET /smooth/v1/menus` takes `page`/`per_page`/`search`, emits `Cache-Control`, schemas cover params + headers + bodies. Rate limiting rides with C11 hardening (noted, not built). Money-path boundary: totals read live item prices; no snapshots, no ledger writes here.

## Risks / Trade-offs

- [Risk] SMO-120 starts before contract stabilizes → Mitigation: contract doc is the first commit (task 0.1); changes after that go through Subha first.
- [Risk] Revision-restore surprise → Mitigation: documented caveat in three places (contract doc, code comment, editor notice if cheap).
- [Risk] `schema` arrays drift from Bruno contracts → Mitigation: same-PR (PR2) `openapi.json` seed + Bruno requests + schema-extract gate.
- [Risk] Unit harness can't cover live WP behavior → Mitigation: stub extensions tasked explicitly (FakeWpdb R/W, blocks/REST/cache/cap stubs); anything still uncovered gets a BLOCKED annotation, not a fake test.
- [Risk] Matrix tests break on context changes → Mitigation: `BootMatrixTest` + `ContextFilterTest` + `ProviderLifecycleTest` updates are PR1 tasks, not afterthoughts.
