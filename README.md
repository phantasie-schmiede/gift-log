# PSBits Gift Log

Keep a record of all the gifts you have received or given.

Gift Log is a TYPO3 extension integrated with PSBits Contact Book. It connects gifts
to their donors, recipients and occasions, and provides a history grouped by year.

## Features

- Record gift descriptions, notes, images and an optional redeemed status.
- Assign multiple donors and recipients from Contact Book.
- Link gifts to Contact Book events or custom Gift Log events, with a year for each relation.
- Keep year-only relations without an assigned event.
- Create, edit and delete gifts in the frontend.
- View gift histories for contacts and events.
- Extend Contact Book contact tabs and event lists with gift information.

## Requirements

- PHP 8.3 or newer, within the Composer constraint `^8.3`.
- TYPO3 13.4 or 14.3, within the Composer constraint `^13.4 || ^14.3`.
- PSBits Foundation 4.2.2 or newer within the 4.x series.
- PSBits Contact Book and its dependencies, including Frontend Templates.

## Installation

```bash
composer require psbits/gift-log
```

Composer installs the required PSBits packages automatically. After installation,
update the TYPO3 database schema and flush the caches.

## Configuration and usage

Configure Contact Book and its frontend integration first. Gift Log uses Contact Book
contacts and events rather than maintaining a separate address book.

The extension provides two frontend plugins:

| Plugin | Purpose |
| --- | --- |
| Gift | Create, edit and delete gifts. |
| History | Display gift histories for a contact or event. |

Plugin registration is handled by Foundation in
`Classes/Data/ExtensionInformation.php`. The contact and event views in Contact Book
link to the Gift Log functionality through the extension's event listeners and templates.

The supplied TypoScript configuration in `Configuration/TypoScript/setup.typoscript`
shares the Contact Book storage page:

```typoscript
plugin.tx_giftlog.persistence.storagePid = {$plugin.tx_contactbook.persistence.storagePid}
```

Ensure `plugin.tx_contactbook.persistence.storagePid` points to the intended record
storage page. Gift Log also uses partials from Contact Book and Frontend Templates;
retain their template configuration when customizing the frontend.

Templates and partials are located in `Resources/Private/Templates/` and
`Resources/Private/Partials/`. English and German language files are provided in
`Resources/Private/Language/`.

## Development

Run development commands from the extension repository, not from the surrounding
TYPO3 project's root. Composer installs development dependencies into `.Build/`.
Node.js 24 or newer and `yamllint` must be available for the frontend and YAML linters.

```bash
composer install
composer docker:images
composer qa
```

Composer's post-install hook installs the npm dependencies and synchronizes the shared
QA configuration and pre-commit hook through `psbits/qa`.

### Quality

`composer qa` runs automatic fixes, all linters and static analysis, followed by the
unit and functional suites and the local and Docker test matrices. It modifies files
through its fixer steps. Use `composer lint:all` for read-only linting and analysis.

| Command | Purpose |
| --- | --- |
| `composer lint:all` | Run all linters, PHPStan, Composer validation and the security audit. |
| `composer fix:all` | Apply automatic formatting and docblock fixes. |
| `composer test:unit` | Run the unit suite against the currently installed core. |
| `composer test:functional` | Run the functional suite against the currently installed core. |
| `composer test:local` | Run TYPO3 13.4 and 14.3 with regular and lowest dependencies using local PHP and SQLite. |
| `composer docker:images` | Build the PHP test images required by the Docker matrix. |
| `composer test:docker` | Run the core matrix in Docker with SQLite, MariaDB and PostgreSQL. |
| `composer test:coverage` | Check coverage against `Build/Quality/coverage-baseline.json`. |

The test matrices resolve dependencies for each core version and require network
access. Docker tests require Docker Compose and a running Docker daemon. The default
Docker test PHP version is 8.4; select another built version with `PHP_VERSION`.
The matrix runners use `.Build/` for their dependencies, so do not run them concurrently
in the same working directory.

When using Xdebug locally, enable coverage explicitly:

```bash
XDEBUG_MODE=coverage composer test:coverage
```

The `CI` GitHub Actions workflow delegates to the shared workflow in `psbits/qa`
and runs quality checks and the supported TYPO3/PHP test matrix. The pre-commit hook
runs `composer lint:all`. Release tags trigger TER publishing, which requires the
`TYPO3_API_TOKEN` repository secret.

### Architecture

- `Classes/Controller/`: frontend gift management and history views.
- `Classes/Domain/`: models, demands and repositories.
- `Classes/EventListener/`: integration with Contact Book views.
- `Tests/Unit/` and `Tests/Functional/`: behavior and persistence tests.

Foundation generates the extension's TCA and database schema from model attributes.
The absence of separate TCA files and `ext_tables.sql` is intentional.

## License

GPL-3.0-or-later. See [LICENSE.txt](LICENSE.txt).
