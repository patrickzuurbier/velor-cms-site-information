<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('site_information', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_information_subject_id')->constrained('site_information_subjects')->cascadeOnDelete();
            $table->string('label');
            $table->string('key');
            $table->string('type');
            $table->text('value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['site_information_subject_id', 'key']);
            $table->index(['site_information_subject_id', 'sort_order']);
        });
    }
};
