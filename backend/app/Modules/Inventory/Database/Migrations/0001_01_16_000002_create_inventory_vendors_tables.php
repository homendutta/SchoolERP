<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vendors and their documents (contracts / invoices — Media references only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });

        Schema::create('vendor_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('vendor_id');
            $table->string('type')->nullable(); // contract | invoice | document
            $table->unsignedBigInteger('media_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
        Schema::dropIfExists('inventory_vendors');
    }
};
