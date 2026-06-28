<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Applied discounts / sibling concessions on a student fee, and the complete
 * scholarship history (independent of discounts).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_discounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_fee_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->unsignedBigInteger('sibling_rule_id')->nullable();
            $table->string('source')->default('discount'); // discount | sibling
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reason')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('student_fee_id');
            $table->index('student_id');
        });

        Schema::create('student_scholarships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('scholarship_id');
            $table->unsignedBigInteger('student_fee_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('awarded_on')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('scholarship_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scholarships');
        Schema::dropIfExists('student_discounts');
    }
};
