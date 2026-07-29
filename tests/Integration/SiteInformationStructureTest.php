<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Tests\Integration;

use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Database\Seeders\SiteInformationTableSeeder;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class SiteInformationStructureTest extends AbstractDatabaseIntegrationTestCase
{
    public function test_it_seeds_default_site_information_subjects_and_fields(): void
    {
        $this->seed(SiteInformationTableSeeder::class);

        $this->assertDatabaseHas('site_information_subjects', [
            'key'          => 'company',
            'name'         => 'Company',
            'is_collapsed' => true,
        ]);
        $this->assertDatabaseHas('site_information_subjects', [
            'key'          => 'location-1',
            'name'         => 'Location 1',
            'is_collapsed' => true,
        ]);
        $this->assertDatabaseHas('site_information', [
            'key'   => 'company-logo',
            'label' => 'Company logo',
            'type'  => SiteInformationFieldTypeEnum::SVG->value,
        ]);
        $this->assertDatabaseHas('site_information', [
            'key'   => 'phone',
            'label' => 'Phone',
            'type'  => SiteInformationFieldTypeEnum::TEXT->value,
        ]);
    }

    public function test_subject_children_and_fields_are_ordered_by_sort_order(): void
    {
        $subject = SiteInformationSubject::factory()->create();
        $last = SiteInformationSubject::factory()->create([
            'parent_id'  => $subject->getKey(),
            'name'       => 'Last',
            'key'        => 'last',
            'sort_order' => 20,
        ]);
        $first = SiteInformationSubject::factory()->create([
            'parent_id'  => $subject->getKey(),
            'name'       => 'First',
            'key'        => 'first',
            'sort_order' => 10,
        ]);

        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Second field',
            'key'                         => 'second-field',
            'sort_order'                  => 20,
        ]);
        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'First field',
            'key'                         => 'first-field',
            'sort_order'                  => 10,
        ]);

        $this->assertSame(
            [$first->getKey(), $last->getKey()],
            $subject->children()->pluck('id')->all(),
        );
        $this->assertSame(
            ['first-field', 'second-field'],
            $subject->siteInformation()->pluck('key')->all(),
        );
    }

    public function test_site_information_type_is_cast_to_enum(): void
    {
        $siteInformation = SiteInformation::factory()->create([
            'type' => SiteInformationFieldTypeEnum::EMAIL,
        ]);

        $this->assertSame(SiteInformationFieldTypeEnum::EMAIL, $siteInformation->getAttribute('type'));
    }

    public function test_subject_collapsed_state_is_editable_data(): void
    {
        $subject = SiteInformationSubject::factory()->create([
            'is_collapsed' => false,
        ]);

        $this->assertFalse($subject->getAttribute('is_collapsed'));

        $subject->update(['is_collapsed' => true]);
        $subject->refresh();

        $this->assertTrue($subject->getAttribute('is_collapsed'));
    }
}
