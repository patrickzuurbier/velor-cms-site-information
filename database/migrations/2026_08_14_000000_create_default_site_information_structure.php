<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;

return new class () extends Migration {
    public function up(): void
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

    protected function location(string $parentId, int $number, int $sortOrder): void
    {
        $location = $this->subject(
            key: 'location-' . $number,
            name: 'Location ' . $number,
            sortOrder: $sortOrder,
            parentId: $parentId,
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
        ?string $parentId = null,
        bool $isCollapsed = true,
    ): string {
        $id = DB::table('site_information_subjects')
            ->where('key', $key)
            ->value('id');

        if (is_string($id)) {
            return $id;
        }

        $id = (string) Str::uuid();

        DB::table('site_information_subjects')->insert([
            'id'           => $id,
            'parent_id'    => $parentId,
            'name'         => $name,
            'key'          => $key,
            'is_collapsed' => $isCollapsed,
            'sort_order'   => $sortOrder,
        ]);

        return $id;
    }

    protected function field(
        string $subjectId,
        string $key,
        string $label,
        SiteInformationFieldTypeEnum $type,
        ?string $value,
        int $sortOrder,
    ): void {
        $existing = DB::table('site_information')
            ->where('site_information_subject_id', $subjectId)
            ->where('key', $key)
            ->exists();

        if ($existing) {
            DB::table('site_information')
                ->where('site_information_subject_id', $subjectId)
                ->where('key', $key)
                ->update([
                    'label'      => $label,
                    'type'       => $type->value,
                    'sort_order' => $sortOrder,
                ]);

            return;
        }

        DB::table('site_information')->insert([
            'id'                          => (string) Str::uuid(),
            'site_information_subject_id' => $subjectId,
            'key'                         => $key,
            'label'                       => $label,
            'type'                        => $type->value,
            'value'                       => $value,
            'sort_order'                  => $sortOrder,
        ]);
    }
};
