<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Tests\Integration;

use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationSubjectRepositoryInterface;

class SiteInformationSubjectRepositoryTest extends AbstractDatabaseIntegrationTestCase
{
    protected SiteInformationSubjectRepositoryInterface $siteInformationSubjectRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteInformationSubjectRepository = $this->app->make(SiteInformationSubjectRepositoryInterface::class);
    }

    public function test_it_creates_a_subject(): void
    {
        $subject = $this->siteInformationSubjectRepository->create([
            'parent_id'    => null,
            'name'         => 'Repository subject',
            'key'          => 'repository-subject',
            'is_collapsed' => false,
            'sort_order'   => null,
        ]);

        $this->assertDatabaseHas('site_information_subjects', [
            'id'   => $subject->getKey(),
            'name' => 'Repository subject',
            'key'  => 'repository-subject',
        ]);
    }

    public function test_it_returns_parent_options_for_root_subjects(): void
    {
        $parent = SiteInformationSubject::factory()->create([
            'name'       => 'Repository parent',
            'key'        => 'repository-parent',
            'sort_order' => 1,
        ]);

        SiteInformationSubject::factory()->create([
            'parent_id'  => $parent->getKey(),
            'name'       => 'Repository child',
            'key'        => 'repository-child',
            'sort_order' => 1,
        ]);

        $options = $this->siteInformationSubjectRepository->parentOptions();

        $this->assertSame((string) $parent->getKey(), $options['Repository parent'] ?? null);
        $this->assertArrayNotHasKey('Repository child', $options);
    }

    public function test_it_reorders_subjects_inside_a_parent_scope(): void
    {
        $parent = SiteInformationSubject::factory()->create();
        $first = SiteInformationSubject::factory()->create([
            'parent_id'  => $parent->getKey(),
            'sort_order' => 1,
        ]);
        $second = SiteInformationSubject::factory()->create([
            'parent_id'  => $parent->getKey(),
            'sort_order' => 2,
        ]);

        $this->siteInformationSubjectRepository->reorderWithinParent((string) $parent->getKey(), [
            (string) $second->getKey(),
            (string) $first->getKey(),
        ]);

        $this->assertSame(2, $first->refresh()->getAttribute('sort_order'));
        $this->assertSame(1, $second->refresh()->getAttribute('sort_order'));
    }
}
