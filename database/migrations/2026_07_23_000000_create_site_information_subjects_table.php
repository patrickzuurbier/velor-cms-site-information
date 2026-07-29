<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('site_information_subjects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('name');
            $table->string('key')->unique();
            $table->boolean('is_collapsed')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
        });

        Schema::table('site_information_subjects', function (Blueprint $table): void {
            $table->foreign('parent_id')->references('id')->on('site_information_subjects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_information_subjects');
    }
};
