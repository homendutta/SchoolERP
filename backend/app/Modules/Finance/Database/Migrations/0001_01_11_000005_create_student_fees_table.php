<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A Fee Structure ASSIGNED to a student. The header carries the computed totals
 * (gross, discount, scholarship, net, paid). Line items are denormalized so a
 * payment never touches a Fee Master.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('scholarship_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('academic_year_id');
            $table->index(['class_id', 'section_id']);
            $table->index('status');
        });

        Schema::create('student_fee_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_fee_id');
            $table->unsignedBigInteger('fee_master_id')->nullable();
            $table->unsignedBigInteger('fee_category_id')->nullable();
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('student_fee_id');
            $table->index('fee_category_id');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_items');
        Schema::dropIfExists('student_fees');
    }
};
