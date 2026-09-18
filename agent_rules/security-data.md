# Security & Data Integrity

> Money-path software. When in doubt, refuse the risky default.

## Secrets and supply chain

- No secrets in the repo, workflows, or committed configs — verified by
  CI grep (`secrets.` count must be 0). `workflow_dispatch` needs no
  credentials; all jobs run on public runners.
- `package-lock.json` is committed (`npm ci` is deterministic);
  `composer.lock` stays ignored — `composer install` without it is
  acceptable, vendoring without review is not.
- Pin third-party GitHub Actions to a major version at minimum
  (`@v4`, `@v1`); note the pin when adding one.

## Data integrity

- Ledger (`smooth_transactions`) is append-only: refunds/voids are new
  rows. Never UPDATE or DELETE ledger rows — reviewer must reject it.
- Totals are server-calculated (`TotalsCalculator` pipeline); never
  trust client-supplied amounts. Money stays in integer cents.
- Idempotency keys on ledger writes; unique keys without defaults.
- Migrations are additive + idempotent + version-guarded
  (`smooth_db_version`); destructive renames never ship in minors;
  backfills run in background jobs, never the request path.

## WordPress surface

- Every repository query goes through `$wpdb->prepare()` (via
  `BaseRepository::prepare()`); no interpolated SQL, no exceptions.
- REST routes live under `smooth/v1` with capability-gated permission
  callbacks (`RestProvider::requireCapability()`); unit context allows,
  production enforces.
- Escape late, sanitize early on all rendered/stored input. Nonces +
  capability checks on every state-changing endpoint.
- Settings persist via the central `Settings` service (autoload off),
  never scattered `update_option()` calls.
