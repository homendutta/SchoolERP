<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable asset categories (parent/child) and reusable asset models. An
 * asset model describes a TYPE of asset; many physical assets reference one
 * model. Depreciation config lives on the model (metadata only — Finance
 * calculates later).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('parent_id');
        });

        Schema::create('asset_models', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('default_warranty_months')->nullable();
            $table->string('depreciation_method')->default('none'); // metadata only
            $table->unsignedInteger('useful_life_years')->nullable();
            $table->decimal('salvage_value', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_models');
        Schema::dropIfExists('asset_categories');
    }
};
