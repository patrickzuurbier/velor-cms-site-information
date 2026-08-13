# Velor CMS Site Information

Site Information is a first-party Velor CMS resource package for managing
website-facing company/contact information in configurable subjects and fields.

This package requires Velor CMS `^1.8`.

## Installation

Require the package:

```bash
composer require patrickzuurbier/velor-cms-site-information
```

Publish and run the migrations when you want editable application copies:

```bash
php artisan vendor:publish --tag=velor-site-information-migrations
php artisan migrate
```

Optionally publish seeders, translations, config, or views:

```bash
php artisan vendor:publish --tag=velor-site-information-seeders
php artisan vendor:publish --tag=velor-site-information-lang
php artisan vendor:publish --tag=velor-site-information-config
php artisan vendor:publish --tag=velor-site-information-views
```

## Features

- Nested subjects with configurable collapsed state.
- Typed fields for text, textarea, email, URL, and SVG markup.
- Singular Site Information show/edit views.
- Structure management for subjects and fields.
- Scoped drag ordering for root subjects, child subjects, and fields.
- SVG markup storage to the media bucket.

## Local Development

For local path development, keep the package repository outside the Velor CMS
core repository. Temporarily point Composer to the package workspace from the
host application. In a separate project it should be installed from its VCS
repository.
