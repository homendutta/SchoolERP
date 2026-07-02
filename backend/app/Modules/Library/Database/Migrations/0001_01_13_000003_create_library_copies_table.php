<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Physical copies — the only borrowable items. Each copy receives its own
 * permanent platform Identity (identity_id); the barcode value and QR payload
 * are generated dynamically by the Identity Platform and read via that relation
 * (never stored as images here).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_copies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('identity_id')->nullable();
            $table->string('copy_number');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('shelf')->nullable();
            $table->string('rack')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->string('condition')->default('good');
            $table->string('status')->default('available');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('book_id');
            $table->index('identity_id');
            $table->index('status');
            $table->unique(['school_id', 'copy_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_copies');
    }
};
