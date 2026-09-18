# Request template — copy, rename, fill in (key names verified against the
# OpenCollection structure reference; re-confirm if Bruno bumps major)

info:
  name: Verb resource            # e.g. "List menus"
  type: http
  seq: 1                         # next free number in this folder (create → get → update)
  tags: [smoke]                  # smoke = CI subset | auth = permission matrix | auth-local = local only

http:
  method: GET                    # uppercase
  url: "{{baseUrl}}/wp-json/smooth/v1/<path>"
  params:
    - name: <param>
      value: "<example>"
      type: query                # query | path
      description: What it does and valid values
  headers:
    - name: Content-Type
      value: application/json
      description: Why this header is sent
  auth: inherit                  # collection-level auth (string form only).
  # OMIT the auth key entirely for public requests: the scalar `none` warns
  # `toBrunoAuth failed` on CLI 4.1.0 (verified 2026-09-18 against the
  # converter source). Explicit authed requests use the block below:
  # auth:
  #   type: basic
  #   username: "{{wpUsername}}"
  #   password: "{{appPassword}}"

runtime:
  assertions:
    - expression: res.status
      operator: eq
      value: "200"
    - expression: res.body.<field>
      operator: isString

settings:
  encodeUrl: true

docs: |-
  ## Purpose
  One paragraph: what this endpoint does and when to call it.

  ## Auth
  Public | capability `smooth_<cap>` (401 without credentials, 403 without cap).

  ## Parameters
  | Name | In | Required | Description |
  | ---- | -- | -------- | ----------- |
  | ...  |    |          |             |

  ## Success example
  ```json
  {}
  ```

  ## Error cases
  - `401` — missing/invalid credentials
  - `403` — authenticated but lacking the cap
  - `404` — unknown id

  ## Notes / Changelog
  - 2026-09-18: created alongside SMO-121
