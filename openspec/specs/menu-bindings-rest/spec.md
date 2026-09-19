# menu-bindings-rest Specification

## Purpose
TBD - created by archiving change menu-bindings-rest. Update Purpose after archive.
## Requirements
### Requirement: Menu table schemas at version 0.2.0
Migration `0.2.0` SHALL create `smooth_menus`, `smooth_menu_items`, and `smooth_modifiers`, with composite keys on hot paths, key columns carrying no defaults, `CURRENT_TIMESTAMP` datetime defaults, and double space after `PRIMARY KEY` for dbDelta. Each table creation SHALL be independently re-runnable.

#### Scenario: Fresh and repeat migration
- **WHEN** the plugin migrates from `0.1.0`, or the `0.2.0` step retries after partial failure
- **THEN** the stored version equals `0.2.0` with all three tables and keys intact, including on multisite via `migrateAll`

### Requirement: Menu repositories behind contracts
The system SHALL provide `MenuRepositoryInterface`, `MenuItemRepositoryInterface`, and `ModifierRepositoryInterface` in `Contracts/`, implemented in `Database/Repositories/` with every variable-interpolating query going through `BaseRepository::prepare()`, bound as singletons in `MenuProvider::register()`.

#### Scenario: Menu read path
- **WHEN** a caller resolves `MenuRepositoryInterface` from the container
- **THEN** it receives the bound singleton and every variable-interpolating query it issues is prepared

### Requirement: Capability via map_meta_cap, public reads
`smooth_manage_menus` SHALL be defined by a `map_meta_cap` filter (owned by `MenuProvider`, hooked for admin+rest contexts) mapping to `manage_options`. `GET` list and single menu reads SHALL be public with pagination (`page`/`per_page`/`search`) and `Cache-Control`; every management action SHALL require the capability, denied otherwise.

#### Scenario: Unauthorized management rejected
- **WHEN** the permission callback runs for a user without the cap
- **THEN** it returns false and no table write occurs

### Requirement: Documented REST schemas
`GET /smooth/v1/menus` and `GET /smooth/v1/menus/<id>` SHALL declare full JSON-Schema `schema` arrays covering params, headers, and every response field, served by a `MenuRoutes` controller in `Domains/Menu/` and delegated from `RestProvider::registerRoutes()`. Item and modifier writes SHALL declare equivalent schemas served by sibling `MenuItemRoutes` and `ModifierRoutes` controllers in `Domains/Menu/`, likewise delegated from `RestProvider::registerRoutes()`.

#### Scenario: Schema validation
- **WHEN** a client fetches a menu
- **THEN** params, headers, and every response field match the declared schema

#### Scenario: Item schema validation
- **WHEN** a client creates or fetches an item or modifier
- **THEN** params and every response field match the declared item/modifier schema

### Requirement: Read-only live bindings in BlocksProvider
The `smooth/menu` bindings source SHALL be registered on `init` in `BlocksProvider` with `label`, `get_value_callback` reading live custom-table rows, and `use_context` for menu/item ids. It SHALL survive reorder, inline edits, and autosave without corruption. Revision restores SHALL NOT be expected to roll back table data (caveat documented in the contract doc, a code comment, and an editor notice if cheap). Editor writes SHALL travel through the cap-gated management routes.

#### Scenario: Autosave during editing
- **WHEN** an autosave fires mid-edit
- **THEN** subsequent renders reflect the live table state with no data loss

### Requirement: Per-request derived memoization
Derived menu HTML/JSON SHALL be memoized per request in `MenuService` with pure key templates (blog id supplied by the caller for multisite namespacing). No menu data SHALL be stored in postmeta. Persistent cache + stampede guard are an explicit follow-up, not this change.

#### Scenario: Repeated reads in one request
- **WHEN** the same menu renders twice in one request
- **THEN** the second render reuses the memoized output and the no-postmeta scan stays green

### Requirement: SMO-120 binding contract first
Task 0.1 SHALL publish `docs/menu-binding-contract.md` (source name, attribute names, types, source args, example markup) as the first commit on the branch so SMO-120 can build against stubs.

#### Scenario: UI build handoff
- **WHEN** Subha starts SMO-120 against the published contract
- **THEN** no SMO-121 implementation detail beyond the contract is needed to author blocks

