<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable Fee Masters — what is owed. A Fee Master defines an amount for a
 * category/year/class at a frequency. Payments NEVER modify Fee Masters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_masters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('fee_category_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('frequency')->default('one_time');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('fee_category_id');
            $table->index('academic_year_id');
            $table->index('class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_masters');
    }
};
