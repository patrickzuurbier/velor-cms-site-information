<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Tests\Integration;

use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Services\SiteInformationPanelFactory;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class SiteInformationStructureTest extends AbstractDatabaseIntegrationTestCase
{
    public function test_it_creates_default_site_information_subjects_and_fields(): void
    {
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

    public function test_site_information_panels_are_ordered_by_sort_order(): void
    {
        SiteInformationSubject::query()->delete();

        $subject = SiteInformationSubject::factory()->create([
            'name'       => 'Subject',
            'key'        => 'subject',
            'sort_order' => 1,
        ]);
        SiteInformationSubject::factory()->create([
            'parent_id'  => $subject->getKey(),
            'name'       => 'Last',
            'key'        => 'last',
            'sort_order' => 2,
        ]);
        SiteInformationSubject::factory()->create([
            'parent_id'  => $subject->getKey(),
            'name'       => 'First',
            'key'        => 'first',
            'sort_order' => 1,
        ]);

        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Second field',
            'key'                         => 'second-field',
            'sort_order'                  => 2,
        ]);
        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'First field',
            'key'                         => 'first-field',
            'sort_order'                  => 1,
        ]);

        /** @var SiteInformationPanelFactory $panelFactory */
        $panelFactory = $this->getApplication()->make(SiteInformationPanelFactory::class);
        $panel = $panelFactory->showPanels()[0];

        $this->assertSame(
            ['First field', 'Second field'],
            array_map(static fn ($attribute): string => $attribute->getLabel(), $panel->getAttributes()),
        );
        $this->assertSame(
            ['First', 'Last'],
            array_map(static fn ($child): ?string => $child->getTitle(), $panel->getPanels()),
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
