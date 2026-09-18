# AGENTS.md — src/Domains/

Pure domain cores, one folder per M1 domain. No `$wpdb`, no WP globals
(enforced by `DomainPurityTest`); WP interaction stays in providers.

- `Shared/Money.php` — cents-only readonly VO; floats only via `fromFloat()`.
- `Shared/DomainEvents.php` — `smooth.*` event names + guarded dispatcher.
  Services dispatch at state transitions; no call sites while shells persist.
- `Checkout/` — `TotalsCalculator` + `TotalStep` pipeline
  (line→discount→tax→fee, filter `smooth_checkout_total_steps`).
- Other domains (`Cart`, `Menu`, `Orders`, `Payments`, `Slots`,
  `Reservations`, `Tables`, `Notifications`) are service shells awaiting
  their follow-up issues — extend, don't restructure.

Rules: `../../agent_rules/coding-conventions.md`,
`../../agent_rules/security-data.md` (money-path).
