<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Database\Factories;

use Velor\SiteInformation\Models\SiteInformationSubject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SiteInformationSubject>
 */
class SiteInformationSubjectFactory extends Factory
{
    protected $model = SiteInformationSubject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word() . ' ' . $this->faker->unique()->word();

        return [
            'parent_id'    => null,
            'name'         => ucfirst($name),
            'key'          => Str::slug($name),
            'is_collapsed' => true,
        ];
    }
}
