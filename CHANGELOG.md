# Changelog

All notable changes to `patrickzuurbier/velor-cms-site-information` will be documented in this file.

This package follows semantic versioning: `MAJOR.MINOR.PATCH`.

## [Unreleased]

## [1.1.3] - 2026-08-14

### Fixed

- Updated Site Information views to use the Velor CMS 1.8 component namespace.

## [1.1.2] - 2026-08-14

### Fixed

- Prevented default Site Information migration and seeder records from creating
  activity log entries.

## [1.1.1] - 2026-08-14

### Added

- Added an idempotent migration-backed default Site Information structure.

### Changed

- Changed the Site Information seeder to preserve existing subjects, fields,
  and values.

### Fixed

- Updated the manage view delete modal component reference for Velor CMS 1.8.

## [1.1.0] - 2026-08-13

### Changed

- Updated the package to use Velor CMS `^1.8` CMS menu registration contracts.

## [1.0.0] - 2026-07-23

### Added

- Extracted the Site Information CMS resource slice into a first-party package.
- Added package-owned models, factories, migrations, seeders, controllers,
  requests, resources, policies, translations, CMS menu registration, CMS
  routes, views, scoped ordering, and SVG storage services.

[Unreleased]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.3...HEAD
[1.1.3]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/patrickzuurbier/velor-cms-site-information/releases/tag/1.0.0
