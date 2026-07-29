<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Database\Factories;

use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\Velor\SiteInformation\Models\SiteInformation>
 */
class SiteInformationFactory extends Factory
{
    protected $model = SiteInformation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->unique()->word() . ' ' . fake()->unique()->word();
        /** @var SiteInformationFieldTypeEnum $type */
        $type = fake()->randomElement(SiteInformationFieldTypeEnum::cases());

        return [
            'site_information_subject_id' => SiteInformationSubject::factory(),
            'label'                       => ucfirst($label),
            'key'                         => Str::slug($label),
            'type'                        => $type->value,
            'value'                       => fake()->sentence(),
            'sort_order'                  => fake()->numberBetween(1, 50),
        ];
    }
}
