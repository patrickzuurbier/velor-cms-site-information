<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Services;

use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;

class SiteInformationDefaultStructureService
{
    public function ensure(): void
    {
        $company = $this->subject('company', 'Company', 10);
        $contact = $this->subject('contact', 'Contact Information', 20);
        $social = $this->subject('social-media', 'Social Media', 30);

        $this->field($company, 'company-name', 'Company name', SiteInformationFieldTypeEnum::TEXT, 'Velor CMS', 10);
        $this->field($company, 'company-logo', 'Company logo', SiteInformationFieldTypeEnum::SVG, null, 20);
        $this->field($company, 'coc', 'Chamber of Commerce', SiteInformationFieldTypeEnum::TEXT, null, 30);
        $this->field($company, 'vat-number', 'VAT number', SiteInformationFieldTypeEnum::TEXT, null, 40);

        $this->location($contact, 1, 10);
        $this->location($contact, 2, 20);
        $this->location($contact, 3, 30);

        $this->field($social, 'facebook', 'Facebook', SiteInformationFieldTypeEnum::URL, null, 10);
        $this->field($social, 'linked-in', 'LinkedIn', SiteInformationFieldTypeEnum::URL, null, 20);
        $this->field($social, 'instagram', 'Instagram', SiteInformationFieldTypeEnum::URL, null, 30);
        $this->field($social, 'pinterest', 'Pinterest', SiteInformationFieldTypeEnum::URL, null, 40);
        $this->field($social, 'youtube', 'YouTube', SiteInformationFieldTypeEnum::URL, null, 50);
    }

    public function remove(): void
    {
        SiteInformationSubject::query()
            ->whereIn('key', ['company', 'contact', 'social-media'])
            ->delete();
    }

    protected function location(SiteInformationSubject $parent, int $number, int $sortOrder): void
    {
        $location = $this->subject(
            key: 'location-' . $number,
            name: 'Location ' . $number,
            sortOrder: $sortOrder,
            parent: $parent,
        );

        $this->field($location, 'street', 'Street', SiteInformationFieldTypeEnum::TEXT, null, 10);
        $this->field($location, 'street-number', 'Street number', SiteInformationFieldTypeEnum::TEXT, null, 20);
        $this->field($location, 'postal-code', 'Postal code', SiteInformationFieldTypeEnum::TEXT, null, 30);
        $this->field($location, 'city', 'City', SiteInformationFieldTypeEnum::TEXT, null, 40);
        $this->field($location, 'email', 'Email', SiteInformationFieldTypeEnum::EMAIL, null, 50);
        $this->field($location, 'phone', 'Phone', SiteInformationFieldTypeEnum::TEXT, null, 60);
    }

    protected function subject(
        string $key,
        string $name,
        int $sortOrder,
        ?SiteInformationSubject $parent = null,
        bool $isCollapsed = true,
    ): SiteInformationSubject {
        return SiteInformationSubject::query()->firstOrCreate(
            ['key' => $key],
            [
                'parent_id'    => $parent?->getKey(),
                'name'         => $name,
                'is_collapsed' => $isCollapsed,
                'sort_order'   => $sortOrder,
            ],
        );
    }

    protected function field(
        SiteInformationSubject $subject,
        string $key,
        string $label,
        SiteInformationFieldTypeEnum $type,
        ?string $value,
        int $sortOrder,
    ): SiteInformation {
        return SiteInformation::query()->firstOrCreate(
            [
                'site_information_subject_id' => $subject->getKey(),
                'key'                         => $key,
            ],
            [
                'label'      => $label,
                'type'       => $type,
                'value'      => $value,
                'sort_order' => $sortOrder,
            ],
        );
    }
}
