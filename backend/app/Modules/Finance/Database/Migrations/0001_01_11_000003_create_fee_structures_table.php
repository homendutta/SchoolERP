<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A Fee Structure combines multiple Fee Masters (e.g. all fees for Class VIII).
 * Students receive Fee Structures, not individual masters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('academic_year_id');
            $table->index('class_id');
        });

        Schema::create('fee_structure_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('fee_structure_id');
            $table->unsignedBigInteger('fee_master_id');
            $table->decimal('amount', 12, 2)->nullable(); // override; null = use master amount
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('fee_structure_id');
            $table->unique(['fee_structure_id', 'fee_master_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structure_items');
        Schema::dropIfExists('fee_structures');
    }
};
