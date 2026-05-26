# This file is for rendering the plugin info on WordPress.org.
# The actual plugin readme is in readme.txt.
# See: https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/

## Development Guide

### Requirements

- **Node.js** 20+ and **npm** 10+
- **PHP** 8.1+
- **Composer**
- **Docker** (for `@wordpress/env` / Playwright E2E)

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/smoothplugins/smooth-restaurant.git
   cd smooth-restaurant
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install JavaScript dependencies:

   ```bash
   npm install
   ```

### Development Workflow

- Start the development build with hot reload:

  ```bash
  npm run dev
  ```

- Create a production build:

  ```bash
  npm run build
  ```

### Testing

- **PHPUnit** (unit and integration tests):

  ```bash
  composer test
  composer test:unit
  composer test:integration
  ```

- **Jest** (JavaScript unit tests):

  ```bash
  npm run test:js
  ```

- **Playwright** (end-to-end tests):

  ```bash
  npm run test:e2e
  npm run test:e2e:ui
  npm run test:e2e:headed
  ```

### Linting

- **PHPCS** (PHP coding standards):

  ```bash
  composer cs:check
  composer cs:fix
  ```

- **PHPStan** (static analysis):

  ```bash
  composer stan
  ```

- **ESLint** (JavaScript/TypeScript):

  ```bash
  npm run lint:js
  ```

- **Stylelint** (CSS/SCSS):

  ```bash
  npm run lint:css
  ```

### Building for Release

1. Ensure all tests pass:

   ```bash
   composer quality
   npm run test:js
   ```

2. Build production assets:

   ```bash
   npm run build
   ```

3. Generate translation files:

   ```bash
   npm run i18n
   ```

4. Package the plugin (exclude `node_modules`, `vendor`, `tests`, `.git`, etc.).

### Contributing

Contributions are welcome! Please:

1. Fork the repository and create a feature branch.
2. Follow the existing code style (WordPress Coding Standards for PHP, `@wordpress/scripts` conventions for JS).
3. Write tests for new features and bug fixes.
4. Ensure all linting and tests pass before submitting a pull request.
5. Update documentation as needed.

### License

Smooth Restaurant is free software licensed under the **GNU General Public License v2.0 or later** (GPL-2.0+).

See [readme.txt](readme.txt) for full license details.
