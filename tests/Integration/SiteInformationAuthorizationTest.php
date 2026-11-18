<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Tests\Integration;

use App\Models\Role;
use App\Models\User;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;

class SiteInformationAuthorizationTest extends AbstractDatabaseIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SiteInformationSubject::query()->delete();

        $role = Role::factory()->create([
            'name'       => 'Unauthorized',
            'slug'       => 'unauthorized',
            'privileges' => [],
        ]);

        $this->actingAs(User::factory()->create([
            'role_id' => $role->getKey(),
        ]));
    }

    public function test_subject_routes_require_their_policy_privileges(): void
    {
        $subject = SiteInformationSubject::factory()->create(['name' => 'Original subject']);

        $this->getJson(route('site-information-subjects.create'))->assertForbidden();
        $this->getJson(route('site-information-subjects.show', $subject))->assertForbidden();
        $this->getJson(route('site-information-subjects.edit', $subject))->assertForbidden();
        $this->postJson(route('site-information-subjects.store'))->assertForbidden();
        $this->putJson(route('site-information-subjects.update', $subject))->assertForbidden();
        $this->deleteJson(route('site-information-subjects.destroy', $subject))->assertForbidden();

        $this->assertSame('Original subject', $subject->refresh()->getAttribute('name'));
        $this->assertSame(1, SiteInformationSubject::query()->count());
    }

    public function test_field_routes_require_their_policy_privileges(): void
    {
        $subject = SiteInformationSubject::factory()->create();
        $field = SiteInformation::factory()->for($subject, 'subject')->create([
            'label' => 'Original field',
        ]);
        $routeParameters = [
            'site_information_subject' => $subject,
            'site_information'         => $field,
        ];

        $this->getJson(route('site-information-subjects.site-information.index', $subject))->assertForbidden();
        $this->getJson(route('site-information-subjects.site-information.create', $subject))->assertForbidden();
        $this->getJson(route('site-information-subjects.site-information.show', $routeParameters))->assertForbidden();
        $this->getJson(route('site-information-subjects.site-information.edit', $routeParameters))->assertForbidden();
        $this->postJson(route('site-information-subjects.site-information.store', $subject))->assertForbidden();
        $this->putJson(route('site-information-subjects.site-information.update', $routeParameters))->assertForbidden();
        $this->deleteJson(route('site-information-subjects.site-information.destroy', $routeParameters))->assertForbidden();

        $this->assertSame('Original field', $field->refresh()->getAttribute('label'));
        $this->assertSame(1, SiteInformation::query()->count());
    }
}
