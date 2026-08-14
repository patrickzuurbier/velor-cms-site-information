<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Database\Seeders;

use Illuminate\Database\Seeder;
use Velor\SiteInformation\Services\SiteInformationDefaultStructureService;

class SiteInformationTableSeeder extends Seeder
{
    public function run(): void
    {
        (new SiteInformationDefaultStructureService())->ensure();
    }
}
