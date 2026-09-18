# AGENTS.md — src/Providers/

One provider per M1 domain (`Menu`…`Blocks`) plus `Core`, `Database`,
`Assets`. All extend `ServiceProvider`; see `../Core/AGENTS.md`.

- `register()` binds ONLY (services + repository interfaces). No hooks,
  no DB, no i18n — enforced by `RegisterPurityTest`.
- `boot()` hooks ONLY: first line bails outside `contexts()`, then
  `markBooted()`. `contexts()` mirrors the boot guard; `Core`/`Database`
  are `['all']`.
- `DatabaseProvider::boot()` (admin only) drains the deferred
  multisite-migration cursor on `admin_init`.
- `AssetsProvider` owns `smooth_should_load()` + `.asset.php` manifests;
  `shouldLoad()`/`manifestData()` are memoized per request.
- `RestProvider::NAMESPACE` is `smooth/v1`; `route()` normalizes paths.

Rules: `../../agent_rules/coding-conventions.md`. Hooks: `smooth_restaurant_`
prefix, underscore style; inventory: `../../HOOKS.md` (add a row per hook).
