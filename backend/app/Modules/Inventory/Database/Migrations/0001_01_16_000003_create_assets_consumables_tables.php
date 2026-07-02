<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Physical assets (each a uniquely identifiable item with its OWN permanent
 * Identity — barcode/QR generated dynamically; asset number from the Number
 * Generator) and consumables (stock items, NOT individually tracked and NEVER
 * given an Identity). Assets and consumables are never mixed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('asset_number');
            $table->unsignedBigInteger('identity_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->unsignedBigInteger('asset_model_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_value', 12, 2)->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->string('condition')->default('good');
            $table->string('status')->default('available');
            $table->unsignedBigInteger('photo_media_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('identity_id');
            $table->index('asset_model_id');
            $table->index('category_id');
            $table->index('status');
            $table->unique(['school_id', 'asset_number']);
        });

        Schema::create('consumables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('unit')->default('unit');
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumables');
        Schema::dropIfExists('assets');
    }
};
