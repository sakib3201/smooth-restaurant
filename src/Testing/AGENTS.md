# AGENTS.md — src/Testing/

Constructor-configurable test doubles with zero WordPress dependencies.
Usable in unit tests, local dev, and E2E seeding.

- `InMemoryGateway` — canned charge/refund results + call recording.
- `FixedSlotAllocator` — canned slot lists.
- `NullNotifier` / `RecordingNotifier` — no-op and assertion-friendly.
- Every fake MUST satisfy its `src/Contracts/` interface (covered by
  `tests/Unit/Testing/FakesTest.php`).

Rules: `../../agent_rules/testing-quality-gates.md`. New contract →
new fake in the same PR.
