<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student Transfers — internal (class/section change) or external (to another
 * school). Append-only history; the move never overwrites prior records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('type'); // internal | external
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->unsignedBigInteger('from_class_id')->nullable();
            $table->unsignedBigInteger('from_section_id')->nullable();
            $table->unsignedBigInteger('to_class_id')->nullable();
            $table->unsignedBigInteger('to_section_id')->nullable();
            $table->date('transfer_date');
            $table->string('reason')->nullable();
            $table->string('destination_school')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
