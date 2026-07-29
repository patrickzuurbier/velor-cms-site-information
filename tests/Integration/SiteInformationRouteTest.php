<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Tests\Integration;

use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Tests\Concerns\UsesAuthorization;
use Tests\Concerns\UsesStorage;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class SiteInformationRouteTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;
    use UsesStorage;

    public function test_site_information_values_can_be_viewed_with_collapsed_subject_panels(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name'         => 'Company',
            'key'          => 'company',
            'is_collapsed' => true,
        ]);
        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Company name',
            'key'                         => 'company-name',
            'type'                        => SiteInformationFieldTypeEnum::TEXT,
            'value'                       => 'Velor CMS',
        ]);

        $response = $this->get(route('site-information.show'));

        $response
            ->assertOk()
            ->assertSee('Company')
            ->assertSee('Company name')
            ->assertSee('Velor CMS')
            ->assertSee('aria-expanded="false"', false)
            ->assertSee(route('site-information.edit'), false);
    }

    public function test_site_information_values_can_be_edited_from_subject_panels(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name'         => 'Contact',
            'key'          => 'contact',
            'is_collapsed' => false,
        ]);
        $field = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Email',
            'key'                         => 'email',
            'type'                        => SiteInformationFieldTypeEnum::EMAIL,
            'value'                       => 'old@example.com',
        ]);

        $response = $this->put(route('site-information.update'), [
            'values' => [
                $field->getKey() => 'info@example.com',
            ],
        ]);

        $response->assertRedirect(route('site-information.show'));
        $response->assertSessionHasNoErrors();

        $field->refresh();

        $this->assertSame('info@example.com', $field->getAttribute('value'));

        $this->get(route('site-information.edit'))
            ->assertOk()
            ->assertSee('Contact')
            ->assertSee('Email')
            ->assertSee('info@example.com')
            ->assertSee('aria-expanded="true"', false);
    }

    public function test_svg_site_information_inputs_show_storage_help_text(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name' => 'Company',
            'key'  => 'company',
        ]);
        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Company logo',
            'key'                         => 'company_logo',
            'type'                        => SiteInformationFieldTypeEnum::SVG,
            'value'                       => null,
        ]);

        $this->get(route('site-information.edit'))
            ->assertOk()
            ->assertSee('company-logo.svg');
    }

    public function test_svg_site_information_values_are_persisted_to_s3_storage(): void
    {
        $this->fakeS3Disk();
        $this->filesystem()->disk('s3')->deleteDirectory('svgs');
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name' => 'Company',
            'key'  => 'company',
        ]);
        $field = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Company logo',
            'key'                         => 'company_logo',
            'type'                        => SiteInformationFieldTypeEnum::SVG,
            'value'                       => null,
        ]);
        $svg = '<svg viewBox="0 0 10 10"><path d="M0 0h10v10H0z"/></svg>';

        $response = $this->put(route('site-information.update'), [
            'values' => [
                $field->getKey() => $svg,
            ],
        ]);

        $response->assertRedirect(route('site-information.show'));
        $response->assertSessionHasNoErrors();

        $field->refresh();

        $this->assertSame($svg, $field->getAttribute('value'));
        $this->assertTrue($this->filesystem()->disk('s3')->exists('svgs/company-logo.svg'));
        $this->assertSame($svg . PHP_EOL, $this->filesystem()->disk('s3')->get('svgs/company-logo.svg'));
    }

    public function test_site_information_structure_can_be_managed_from_subject_panels(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name'         => 'Company',
            'key'          => 'company',
            'is_collapsed' => false,
        ]);
        $child = SiteInformationSubject::factory()->create([
            'parent_id'    => $subject->getKey(),
            'name'         => 'Location 1',
            'key'          => 'location-1',
            'is_collapsed' => true,
        ]);
        $field = SiteInformation::factory()->create([
            'site_information_subject_id' => $child->getKey(),
            'label'                       => 'Email',
            'key'                         => 'email',
            'type'                        => SiteInformationFieldTypeEnum::EMAIL,
            'value'                       => 'info@example.com',
        ]);

        $this->get(route('site-information.manage'))
            ->assertOk()
            ->assertSee('Company')
            ->assertSee('Location 1')
            ->assertSee('Email')
            ->assertSee(route('site-information-subjects.edit', ['site_information_subject' => $subject->getKey()]), false)
            ->assertSee(route('site-information-subjects.site-information.edit', [
                'site_information_subject' => $child->getKey(),
                'site_information'         => $field->getKey(),
            ]), false)
            ->assertSee('All child subjects, fields, entered values, and generated SVG files', false);
    }

    public function test_site_information_values_are_validated_by_field_type(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create();
        $field = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Email',
            'key'                         => 'email',
            'type'                        => SiteInformationFieldTypeEnum::EMAIL,
            'value'                       => null,
        ]);

        $response = $this->put(route('site-information.update'), [
            'values' => [
                $field->getKey() => 'invalid-email',
            ],
        ]);

        $response->assertSessionHasErrors([
            'values.' . $field->getKey(),
        ]);
    }

    public function test_subjects_can_be_created_and_viewed(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('site-information-subjects.store'), [
            'parent_id'    => null,
            'name'         => 'Company',
            'is_collapsed' => '1',
            'sort_order'   => '10',
        ]);

        $subject = SiteInformationSubject::query()->where('key', 'company')->first();

        $this->assertInstanceOf(SiteInformationSubject::class, $subject);
        $response->assertRedirect(route('site-information.manage'));
        $this->assertTrue($subject->getAttribute('is_collapsed'));

        $this->get(route('site-information-subjects.show', [
            'site_information_subject' => $subject->getKey(),
        ]))
            ->assertOk()
            ->assertSee('Company')
            ->assertSee(route('site-information-subjects.site-information.index', [
                'site_information_subject' => $subject->getKey(),
            ]), false);
    }

    public function test_subjects_cannot_be_moved_below_themselves(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name' => 'Company',
            'key'  => 'company',
        ]);

        $response = $this->patch(route('site-information-subjects.update', [
            'site_information_subject' => $subject->getKey(),
        ]), [
            'parent_id'    => $subject->getKey(),
            'name'         => 'Company',
            'is_collapsed' => '0',
            'sort_order'   => '10',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_subjects_with_children_cannot_be_moved_below_another_subject(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'parent_id' => null,
        ]);
        SiteInformationSubject::factory()->create([
            'parent_id' => $subject->getKey(),
        ]);
        $target = SiteInformationSubject::factory()->create([
            'parent_id' => null,
        ]);

        $response = $this->patch(route('site-information-subjects.update', [
            'site_information_subject' => $subject->getKey(),
        ]), [
            'parent_id'    => $target->getKey(),
            'name'         => (string) $subject->getAttribute('name'),
            'is_collapsed' => '0',
            'sort_order'   => '10',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_deleting_a_subject_removes_svg_files_for_its_fields_and_child_fields(): void
    {
        $this->fakeS3Disk();
        $this->filesystem()->disk('s3')->deleteDirectory('svgs');
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'parent_id' => null,
        ]);
        $child = SiteInformationSubject::factory()->create([
            'parent_id' => $subject->getKey(),
        ]);
        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Company logo',
            'key'                         => 'company_logo',
            'type'                        => SiteInformationFieldTypeEnum::SVG,
            'value'                       => '<svg viewBox="0 0 10 10"></svg>',
        ]);
        SiteInformation::factory()->create([
            'site_information_subject_id' => $child->getKey(),
            'label'                       => 'Location logo',
            'key'                         => 'location_logo',
            'type'                        => SiteInformationFieldTypeEnum::SVG,
            'value'                       => '<svg viewBox="0 0 10 10"></svg>',
        ]);
        $this->filesystem()->disk('s3')->put('svgs/company-logo.svg', '<svg></svg>');
        $this->filesystem()->disk('s3')->put('svgs/location-logo.svg', '<svg></svg>');

        $response = $this->delete(route('site-information-subjects.destroy', [
            'site_information_subject' => $subject->getKey(),
        ]));

        $response->assertRedirect(route('site-information.manage'));
        $this->assertFalse($this->filesystem()->disk('s3')->exists('svgs/company-logo.svg'));
        $this->assertFalse($this->filesystem()->disk('s3')->exists('svgs/location-logo.svg'));
    }

    public function test_fields_can_be_created_for_a_subject(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name' => 'Contact',
            'key'  => 'contact',
        ]);

        $response = $this->post(route('site-information-subjects.site-information.store', [
            'site_information_subject' => $subject->getKey(),
        ]), [
            'label'      => 'Email',
            'type'       => SiteInformationFieldTypeEnum::EMAIL->value,
            'value'      => 'info@example.com',
            'sort_order' => '10',
        ]);

        $field = SiteInformation::query()->where('key', 'contact.email')->first();

        $this->assertInstanceOf(SiteInformation::class, $field);
        $this->assertSame($subject->getKey(), $field->getAttribute('site_information_subject_id'));
        $response->assertRedirect(route('site-information.manage'));
    }

    public function test_field_create_form_prefills_the_next_sort_order(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create();
        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'sort_order'                  => 7,
        ]);

        $this->get(route('site-information-subjects.site-information.create', [
            'site_information_subject' => $subject->getKey(),
        ]))
            ->assertOk()
            ->assertSee('value="8"', false);
    }

    public function test_field_create_uses_the_next_sort_order_when_it_is_left_empty(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name' => 'Contact',
            'key'  => 'contact',
        ]);
        SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'sort_order'                  => 3,
        ]);

        $response = $this->post(route('site-information-subjects.site-information.store', [
            'site_information_subject' => $subject->getKey(),
        ]), [
            'label'      => 'Bank account',
            'type'       => SiteInformationFieldTypeEnum::TEXT->value,
            'value'      => 'NLRABO012345678',
            'sort_order' => null,
        ]);

        $field = SiteInformation::query()->where('key', 'contact.bank_account')->first();

        $this->assertInstanceOf(SiteInformation::class, $field);
        $this->assertSame(4, $field->getAttribute('sort_order'));
        $response->assertRedirect(route('site-information.manage'));
    }

    public function test_fields_created_for_child_subjects_get_a_nested_dot_key(): void
    {
        $this->actingAsAdmin();
        $parent = SiteInformationSubject::factory()->create([
            'name' => 'Company',
            'key'  => 'company',
        ]);
        $child = SiteInformationSubject::factory()->create([
            'parent_id' => $parent->getKey(),
            'name'      => 'Location 1',
            'key'       => 'location_1',
        ]);

        $response = $this->post(route('site-information-subjects.site-information.store', [
            'site_information_subject' => $child->getKey(),
        ]), [
            'label'      => 'Street',
            'type'       => SiteInformationFieldTypeEnum::TEXT->value,
            'value'      => 'Main street',
            'sort_order' => '10',
        ]);

        $field = SiteInformation::query()->where('key', 'company.location_1.street')->first();

        $this->assertInstanceOf(SiteInformation::class, $field);
        $response->assertRedirect(route('site-information.manage'));
    }

    public function test_svg_fields_are_persisted_to_s3_storage_when_managed_as_fields(): void
    {
        $this->fakeS3Disk();
        $this->filesystem()->disk('s3')->deleteDirectory('svgs');
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create([
            'name' => 'Company',
            'key'  => 'company',
        ]);
        $svg = '<svg viewBox="0 0 10 10"><path d="M0 0h10v10H0z"/></svg>';

        $response = $this->post(route('site-information-subjects.site-information.store', [
            'site_information_subject' => $subject->getKey(),
        ]), [
            'label'      => 'Company logo',
            'type'       => SiteInformationFieldTypeEnum::SVG->value,
            'value'      => $svg,
            'sort_order' => '10',
        ]);

        $field = SiteInformation::query()->where('key', 'company.company_logo')->first();

        $this->assertInstanceOf(SiteInformation::class, $field);
        $response->assertRedirect(route('site-information.manage'));
        $this->assertTrue($this->filesystem()->disk('s3')->exists('svgs/company-logo.svg'));
        $this->assertSame($svg . PHP_EOL, $this->filesystem()->disk('s3')->get('svgs/company-logo.svg'));
    }

    public function test_svg_field_updates_keep_the_stable_generated_key_and_filename(): void
    {
        $this->fakeS3Disk();
        $this->filesystem()->disk('s3')->deleteDirectory('svgs');
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create();
        $field = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Company logo',
            'key'                         => 'company_logo',
            'type'                        => SiteInformationFieldTypeEnum::SVG,
            'value'                       => '<svg viewBox="0 0 10 10"></svg>',
        ]);
        $this->filesystem()->disk('s3')->put('svgs/company-logo.svg', '<svg></svg>');

        $response = $this->patch(route('site-information-subjects.site-information.update', [
            'site_information_subject' => $subject->getKey(),
            'site_information'         => $field->getKey(),
        ]), [
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Header logo',
            'type'                        => SiteInformationFieldTypeEnum::SVG->value,
            'value'                       => '<svg viewBox="0 0 20 20"></svg>',
            'sort_order'                  => '10',
        ]);

        $response->assertRedirect(route('site-information.manage'));
        $this->assertTrue($this->filesystem()->disk('s3')->exists('svgs/company-logo.svg'));
        $this->assertFalse($this->filesystem()->disk('s3')->exists('svgs/header-logo.svg'));
        $this->assertSame(
            '<svg viewBox="0 0 20 20"></svg>' . PHP_EOL,
            $this->filesystem()->disk('s3')->get('svgs/company-logo.svg'),
        );

        $field->refresh();

        $this->assertSame('company_logo', $field->getAttribute('key'));
    }

    public function test_svg_field_deletion_removes_the_s3_file(): void
    {
        $this->fakeS3Disk();
        $this->filesystem()->disk('s3')->deleteDirectory('svgs');
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create();
        $field = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'label'                       => 'Company logo',
            'key'                         => 'company_logo',
            'type'                        => SiteInformationFieldTypeEnum::SVG,
            'value'                       => '<svg viewBox="0 0 10 10"></svg>',
        ]);
        $this->filesystem()->disk('s3')->put('svgs/company-logo.svg', '<svg></svg>');

        $response = $this->delete(route('site-information-subjects.site-information.destroy', [
            'site_information_subject' => $subject->getKey(),
            'site_information'         => $field->getKey(),
        ]));

        $response->assertRedirect(route('site-information.manage'));
        $this->assertFalse($this->filesystem()->disk('s3')->exists('svgs/company-logo.svg'));
    }

    public function test_fields_can_be_moved_to_another_subject(): void
    {
        $this->actingAsAdmin();
        $source = SiteInformationSubject::factory()->create();
        $target = SiteInformationSubject::factory()->create();
        $field = SiteInformation::factory()->create([
            'site_information_subject_id' => $source->getKey(),
            'label'                       => 'Phone',
            'key'                         => 'phone',
            'type'                        => SiteInformationFieldTypeEnum::TEXT,
            'value'                       => '+31 10 123 4567',
        ]);

        $response = $this->patch(route('site-information-subjects.site-information.update', [
            'site_information_subject' => $source->getKey(),
            'site_information'         => $field->getKey(),
        ]), [
            'site_information_subject_id' => $target->getKey(),
            'label'                       => 'Phone',
            'type'                        => SiteInformationFieldTypeEnum::TEXT->value,
            'value'                       => '+31 10 123 4567',
            'sort_order'                  => '20',
        ]);

        $field->refresh();

        $this->assertSame($target->getKey(), $field->getAttribute('site_information_subject_id'));
        $this->assertSame('phone', $field->getAttribute('key'));
        $response->assertRedirect(route('site-information.manage'));
    }

    public function test_root_subjects_can_be_reordered(): void
    {
        $this->actingAsAdmin();
        $first = SiteInformationSubject::factory()->create([
            'parent_id'  => null,
            'sort_order' => 1,
        ]);
        $second = SiteInformationSubject::factory()->create([
            'parent_id'  => null,
            'sort_order' => 2,
        ]);

        $response = $this->postJson(route('site-information.reorder'), [
            'type'    => 'subject',
            'context' => null,
            'items'   => [
                (string) $second->getKey(),
                (string) $first->getKey(),
            ],
        ]);

        $response->assertNoContent();
        $this->assertSame(2, $first->refresh()->getAttribute('sort_order'));
        $this->assertSame(1, $second->refresh()->getAttribute('sort_order'));
    }

    public function test_child_subjects_can_only_be_reordered_inside_their_parent(): void
    {
        $this->actingAsAdmin();
        $parent = SiteInformationSubject::factory()->create();
        $otherParent = SiteInformationSubject::factory()->create();
        $first = SiteInformationSubject::factory()->create([
            'parent_id'  => $parent->getKey(),
            'sort_order' => 1,
        ]);
        $second = SiteInformationSubject::factory()->create([
            'parent_id'  => $parent->getKey(),
            'sort_order' => 2,
        ]);
        $other = SiteInformationSubject::factory()->create([
            'parent_id'  => $otherParent->getKey(),
            'sort_order' => 1,
        ]);

        $response = $this->postJson(route('site-information.reorder'), [
            'type'    => 'subject',
            'context' => (string) $parent->getKey(),
            'items'   => [
                (string) $second->getKey(),
                (string) $first->getKey(),
                (string) $other->getKey(),
            ],
        ]);

        $response->assertNoContent();
        $this->assertSame(2, $first->refresh()->getAttribute('sort_order'));
        $this->assertSame(1, $second->refresh()->getAttribute('sort_order'));
        $this->assertSame(1, $other->refresh()->getAttribute('sort_order'));
    }

    public function test_fields_can_only_be_reordered_inside_their_subject(): void
    {
        $this->actingAsAdmin();
        $subject = SiteInformationSubject::factory()->create();
        $otherSubject = SiteInformationSubject::factory()->create();
        $first = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'sort_order'                  => 1,
        ]);
        $second = SiteInformation::factory()->create([
            'site_information_subject_id' => $subject->getKey(),
            'sort_order'                  => 2,
        ]);
        $other = SiteInformation::factory()->create([
            'site_information_subject_id' => $otherSubject->getKey(),
            'sort_order'                  => 1,
        ]);

        $response = $this->postJson(route('site-information.reorder'), [
            'type'    => 'field',
            'context' => (string) $subject->getKey(),
            'items'   => [
                (string) $second->getKey(),
                (string) $first->getKey(),
                (string) $other->getKey(),
            ],
        ]);

        $response->assertNoContent();
        $this->assertSame(2, $first->refresh()->getAttribute('sort_order'));
        $this->assertSame(1, $second->refresh()->getAttribute('sort_order'));
        $this->assertSame(1, $other->refresh()->getAttribute('sort_order'));
    }
}
