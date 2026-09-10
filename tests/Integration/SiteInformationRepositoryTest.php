<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Tests\Integration;

use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationRepositoryInterface;

class SiteInformationRepositoryTest extends AbstractDatabaseIntegrationTestCase
{
    protected SiteInformationRepositoryInterface $siteInformationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteInformationRepository = $this->getApplication()->make(SiteInformationRepositoryInterface::class);
    }

    public function test_it_creates_a_field_for_a_subject(): void
    {
        $subject = SiteInformationSubject::factory()->create();

        $siteInformation = $this->siteInformationRepository->createForSubject($subject, [
            'label'      => 'Email address',
            'key'        => 'contact.email_address',
            'type'       => SiteInformationFieldTypeEnum::EMAIL->value,
            'value'      => 'info@example.com',
            'sort_order' => null,
        ]);

        $this->assertDatabaseHas('site_information', [
            'id'                          => $siteInformation->getKey(),
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Email address',
            'key'                         => 'contact.email_address',
        ]);
    }

    public function test_it_updates_a_field_value(): void
    {
        $siteInformation = SiteInformation::factory()->create([
            'value' => 'Old value',
        ]);

        $updatedSiteInformation = $this->siteInformationRepository->updateValue($siteInformation, 'New value');

        $this->assertSame('New value', $updatedSiteInformation->getAttribute('value'));
    }

    public function test_it_reorders_fields_inside_a_subject(): void
    {
        $subject = SiteInformationSubject::factory()->create();
        $first = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'sort_order'                  => 1,
        ]);
        $second = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'sort_order'                  => 2,
        ]);

        $this->siteInformationRepository->reorderWithinSubject((string) $subject->getKey(), [
            (string) $second->getKey(),
            (string) $first->getKey(),
        ]);

        $this->assertSame(2, $first->refresh()->getAttribute('sort_order'));
        $this->assertSame(1, $second->refresh()->getAttribute('sort_order'));
    }
}
