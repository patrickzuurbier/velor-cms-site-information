# Velor CMS Site Information

Site Information is a first-party Velor CMS package for structured,
website-facing data such as company details, contact information, locations,
social links, and reusable SVG assets. It is separate from authenticated user
settings and from application configuration.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Package Registration](#package-registration)
- [Data Model](#data-model)
- [Field Types](#field-types)
- [Reading Site Information](#reading-site-information)
- [Default Structure](#default-structure)
- [SVG Storage](#svg-storage)
- [Publishing Package Files](#publishing-package-files)
- [License](#license)

## Requirements

- PHP `^8.4.1`
- Velor CMS `^1.18`

## Installation

Install the package through Composer:

```bash
composer require patrickzuurbier/velor-cms-site-information:^1.4
```

Laravel discovers `SiteInformationServiceProvider` automatically. Run the
package migrations with the host application migrations:

```bash
php artisan migrate
```

## Package Registration

The package service provider registers:

- the Site Information subject and field resources;
- their policies and role privileges;
- the authenticated CMS routes and sidebar item;
- repository interface bindings;
- package migrations, translations, and views.

Installing the package always enables these integrations. The package has no
enable/disable configuration option.

## Data Model

Site Information uses two Eloquent models:

| Model | Purpose |
| --- | --- |
| `Velor\SiteInformation\Models\SiteInformationSubject` | An ordered group with a stable key and default collapsed state. |
| `Velor\SiteInformation\Models\SiteInformation` | An ordered, typed, nullable value inside a subject. |

Subjects may be roots or children of a root subject. The package only accepts
root subjects as parents, which limits the managed hierarchy to two levels.
Subject ordering is scoped by `parent_id`; field ordering is scoped by
`site_information_subject_id`.

Deleting a subject cascades to its child subjects and fields. Both models use
UUID primary keys and participate in Velor CMS auditing and row ordering.

## Field Types

`Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum` defines the
supported value types:

| Type | Validation |
| --- | --- |
| `text` | Nullable string with a maximum of 255 characters. |
| `textarea` | Nullable string with a maximum of 255 characters. |
| `email` | Nullable email address with a maximum of 255 characters. |
| `url` | Nullable URL with a maximum of 2048 characters. |
| `svg` | Nullable SVG markup accepted by the core `SvgMarkup` rule. |

Phone numbers, postal codes, and other organization-specific formats use
`text`.

## Reading Site Information

Application code can inject
`Velor\SiteInformation\Repositories\Contracts\SiteInformationRepositoryInterface`.
Its `all()` method returns all `SiteInformation` models; use each model's
stable `key`, cast `type`, nullable `value`, and `subject` relationship to map
the package data into website view data.

```php
use Velor\SiteInformation\Repositories\Contracts\SiteInformationRepositoryInterface;

final class SiteInformationViewData
{
    public function __construct(
        private SiteInformationRepositoryInterface $siteInformation,
    ) {
    }

    public function values(): array
    {
        return $this->siteInformation
            ->all()
            ->pluck('value', 'key')
            ->all();
    }
}
```

The repository contract is bound by the package service provider; consumers do
not need to bind its concrete implementation.

## Default Structure

The package migrations create an idempotent default structure with these root
subjects:

- Company;
- Contact Information, with Location 1, Location 2, and Location 3 children;
- Social Media.

The structure includes common company, address, contact, and social fields.
Existing field values are preserved when the default-structure migration finds
matching records.

## SVG Storage

SVG values remain in the `site_information.value` database column. The package
also writes each non-empty SVG value to the Laravel `s3` filesystem disk at:

```text
svgs/{field-key}.svg
```

The filename uses a slug of the final dot-separated segment of the field key.
For the default `company-logo` key, the path is
`svgs/company-logo.svg`.

Clearing or deleting an SVG field removes its file. Changing a field away from
the SVG type removes its previous file. Deleting a subject also removes SVG
files owned by that subject and its direct children before database cascades
remove the records.

## Publishing Package Files

The package loads its own migrations, translations, and views by default.
Publish only files the host application intentionally needs to own or override:

```bash
php artisan vendor:publish --tag=velor-site-information-migrations
php artisan vendor:publish --tag=velor-site-information-lang
php artisan vendor:publish --tag=velor-site-information-views
```

Published files become application-owned copies and no longer receive package
changes automatically.

## License

Velor CMS Site Information is open-sourced software licensed under the MIT
license.
