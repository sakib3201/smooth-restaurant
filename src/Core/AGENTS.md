# AGENTS.md — src/Core/

Plugin bootstrap and cross-cutting services. No domain logic here.

- `Plugin` — singleton; `registerProviders()` builds the Free list,
  applies the additive-only `smooth_restaurant_service_providers` filter, validates
  (class/interface/version floor), restores removals. Invalid entries are
  logged, never fatal.
- `Container` — split `$instances` vs `$providers`; `bind`/`singleton`/
  `instance()` (test doubles) / `make()` / `has()`; reflection is a
  boot-time fallback, not a hot path.
- `ServiceProvider` — `VERSION` floor, `register()` bind-only,
  `boot()` hook-only with early bail, `markBooted()`.
- `Context::current()` — admin/rest/cron/frontend/cli detection for
  registration gating (all checks guarded for unit context).
- `Settings` (central `smooth_settings`, autoload off),
  `WpLogger` (LoggerInterface → error_log), `WpCronJobQueue`
  (JobQueueInterface; Action Scheduler later), Activator/Deactivator.

Rules: `../../agent_rules/coding-conventions.md`,
`../../agent_rules/security-data.md`.
