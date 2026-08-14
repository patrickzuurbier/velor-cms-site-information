<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Velor\SiteInformation\Services\SiteInformationDefaultStructureService;

return new class () extends Migration {
    public function up(): void
    {
        (new SiteInformationDefaultStructureService())->ensure();
    }

    public function down(): void
    {
        (new SiteInformationDefaultStructureService())->remove();
    }
};
