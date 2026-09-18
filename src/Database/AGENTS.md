# AGENTS.md — src/Database/

The only directory allowed to touch `$wpdb`. Everything else reads
through repositories.

- `MigrationRunner` — canonical `smooth_db_version`; version-guarded,
  idempotent, additive; `migrateAll($networkWide, $batchSize)` defers
  overflow sites to the `smooth_migrations_pending` cursor drained on
  `admin_init`. Downgrade-safe reads; no down-migrations by policy.
- `BaseRepository` — prefix, `prepare()`, hydration, dbDelta-first
  `schema()`/`createTable()`. Nullable `$wpdb` ctor falls back to
  `$GLOBALS['wpdb']`, then a bare default (unit context).
- `Repositories/` — 9 tables, each `implements` its `Contracts/`
  interface. Schema rules: no `DEFAULT ''` under UNIQUE, no zero-dates,
  composite KEYs on hot paths, two spaces after `KEY` for dbDelta.
- Ledger (`TransactionRepository`) is append-only; refunds are new rows.

Rules: `../../agent_rules/coding-conventions.md`,
`../../agent_rules/security-data.md`, `../../agent_rules/testing-quality-gates.md`
(migration changes need matching tests).
