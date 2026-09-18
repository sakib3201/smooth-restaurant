# Folder Structure

> Modular monolith. One provider per M1 domain; pure cores; `$wpdb`
> confined to `src/Database/`.

```text
smooth-restaurant.php      Entrypoint: constants, hooks, Plugin::boot()
src/
  Core/                    Plugin, Container, ServiceProvider, Context,
                           Activator/Deactivator, Settings, WpLogger,
                           WpCronJobQueue
  Providers/               One per domain (Menu…Blocks) + Core, Database,
                           Assets. register() binds; boot() hooks+gates.
  Domains/<D>/             Pure domain services (+ Shared/: Money,
                           TotalsCalculator+steps, DomainEvents)
  Contracts/               Gateway/Slots/Notifier/Logger/JobQueue +
                           per-table repository interfaces
  Database/                MigrationRunner, BaseRepository, Repositories/
                           (9 tables), no raw SQL in request path
  Exceptions/              SmoothException → Unresolvable/Repository/Migration
  Testing/                 InMemoryGateway, FixedSlotAllocator,
                           Null/RecordingNotifier (zero WP deps)
assets/src/{admin,frontend,shared,blocks}/  TS entries (@/ aliases);
                           shared/content-gate.ts mirrors smooth_should_load()
tests/{Unit,Integration,e2e,js}/  PHPUnit suites, Playwright, Jest
openspec/{specs,changes/}  Source of truth specs + ephemeral changes
openspec/changes/archive/  Dated archives (YYYY-MM-DD-<name>)
agent_rules/               This harness (guardrails, this file's siblings)
.github/                   7 workflows + BRANCH_PROTECTION.md + PR template
sm_docs/                   Product/design source of truth (read, don't move)
```

## Placement rules

- New domain logic → `src/Domains/<D>/`; new WP seam → its provider;
  new table → `Repositories/` + interface in `Contracts/` + binding in
  the owning provider's `register()`.
- Tests mirror source paths under `tests/Unit/` (or Integration/e2e/js).
- Planning artifacts → `openspec/changes/<name>/`; agent guardrails →
  `agent_rules/`; per-directory context → `AGENTS.md` next to the code.
