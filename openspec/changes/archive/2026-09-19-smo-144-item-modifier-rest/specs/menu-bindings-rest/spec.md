## MODIFIED Requirements

### Requirement: Documented REST schemas

`GET /smooth/v1/menus` and `GET /smooth/v1/menus/<id>` SHALL declare full JSON-Schema `schema` arrays covering params, headers, and every response field, served by a `MenuRoutes` controller in `Domains/Menu/` and delegated from `RestProvider::registerRoutes()`. Item and modifier writes SHALL declare equivalent schemas served by sibling `MenuItemRoutes` and `ModifierRoutes` controllers in `Domains/Menu/`, likewise delegated from `RestProvider::registerRoutes()`.

#### Scenario: Schema validation

- **WHEN** a client fetches a menu
- **THEN** params, headers, and every response field match the declared schema

#### Scenario: Item schema validation

- **WHEN** a client creates or fetches an item or modifier
- **THEN** params and every response field match the declared item/modifier schema
