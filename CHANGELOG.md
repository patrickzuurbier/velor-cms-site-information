# Changelog

All notable changes to `patrickzuurbier/velor-cms-site-information` will be documented in this file.

This package follows semantic versioning: `MAJOR.MINOR.PATCH`.

## [Unreleased]

### Changed

- Updated controllers, package views, and resource registration for the Velor
  CMS `^1.9` resource-first contract.
- Removed obsolete empty package config publishing.
- Removed the explicit Composer version field so package tags remain the
  version source of truth.

## [1.2.1] - 2026-08-21

### Changed

- Changed Site Information subject and field order inputs to the dedicated
  Velor CMS Order field.
- Removed the enabled option from config; package registration is now always
  active when the package is installed.
- Moved subject and field presentation ordering out of model relationships and
  into package query/panel composition.
- Split Site Information input and attribute DTO creation out of the panel
  factory.
- Moved default Site Information structure creation fully into the migration
  and removed the package seeder and default structure service.

### Fixed

- Removed random subject and field sort order values from factories so row
  ordering can fill compact positions.
- Compacted Site Information drag ordering positions after reordering.
- Fixed Site Information row order defaults and field subject moves.
- Fixed panel factory relation types for PHPStan.

## [1.2.0] - 2026-08-15

### Changed

- Improved the Site Information manage flow with scoped child subject and field
  indexes, parent back links, and context-aware redirects after saving.
- Moved subject deletion into the subject show action row with a clearer
  subject-specific confirmation title.

### Fixed

- Filled missing subject sort order values inside their parent scope.
- Removed raw HTML from Site Information delete confirmation messages.

## [1.1.4] - 2026-08-14

### Fixed

- Updated the Site Information form view to use the Velor CMS 1.8 form panel
  component namespace.

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

[Unreleased]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.2.1...HEAD
[1.2.1]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.4...1.2.0
[1.1.4]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/patrickzuurbier/velor-cms-site-information/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/patrickzuurbier/velor-cms-site-information/releases/tag/1.0.0
