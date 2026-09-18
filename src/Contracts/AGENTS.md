# AGENTS.md — src/Contracts/

Public seams Pro and tests program against. Interfaces only — no logic.

- Domain contracts: `GatewayInterface`, `SlotAllocatorInterface`,
  `NotifierInterface` (+ fakes in `src/Testing/`).
- Platform contracts: `LoggerInterface`, `JobQueueInterface`.
- Repository contracts: one per table (`*RepositoryInterface`),
  implemented by `src/Database/Repositories/*`, bound in the owning
  domain provider — decoration/replacement goes through
  `Container::instance()`, never subclassing.
- Stability: treat these as `@api`. Additive changes only; changing a
  signature is breaking and needs a Linear issue + spec.

Rules: `../../agent_rules/coding-conventions.md`.
