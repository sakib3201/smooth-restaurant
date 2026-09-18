# data-access Specification

## Purpose
TBD - created by archiving change core-provider-architecture. Update Purpose after archive.
## Requirements
### Requirement: Shared table-access pattern

All custom-table access SHALL go through a shared `BaseRepository` over `$wpdb` owning table prefix, charset/collate, statement preparation, and typed-row mapping. Per-table repositories in each domain SHALL implement queries; raw `$wpdb` usage outside repositories is forbidden. Schema creation SHALL be dbDelta-first, with raw SQL only for indexes or keys `dbDelta` cannot express.

#### Scenario: Domain query flows through the base

- **WHEN** a domain reads or writes its table
- **THEN** it calls its repository, which delegates connection, prefix, and preparation to `BaseRepository`

#### Scenario: Raw wpdb outside repos fails review

- **WHEN** a PR adds direct `$wpdb` calls outside `Database/` or a repository class
- **THEN** the architecture test or review flags the violation

### Requirement: Pro data boundaries

Pro SHALL read Free tables only through Free repository interfaces and SHALL perform writes only through Free services or its own tables. Direct Free-table writes from Pro are forbidden; the ledger stays append-only through Free APIs.

#### Scenario: Pro reuses Free reads

- **WHEN** Pro needs order data
- **THEN** it resolves the Free order repository interface rather than querying the table directly

### Requirement: No postmeta for transactional data

Transactional entities (orders, order items, transactions, reservations, tables, sessions, carts, coupons, notifications) SHALL NOT use postmeta, `meta_query`, or post-meta write APIs. A PHPUnit architecture test over `src/Domains/` and `src/Database/` SHALL fail on violation. The sole exception is an optional read-only menu CPT mirror for display or SEO, which SHALL never be a source of truth.

#### Scenario: Postmeta sneaks into a domain

- **WHEN** a PR adds `add_post_meta`, `get_post_meta`, or `meta_query` usage for an order or reservation path
- **THEN** the architecture test fails before merge

#### Scenario: Mirror stays read-only

- **WHEN** the menu CPT mirror syncs
- **THEN** writes flow one way (tables to CPT) and no order, payment, or reservation path reads the mirror

