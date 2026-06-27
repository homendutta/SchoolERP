<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Teacher -> Academic Year -> Class -> Section -> Subject (multiple teachers allowed)
        Schema::create('teacher_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['academic_year_id', 'class_id', 'section_id', 'subject_id', 'teacher_id'],
                'tsa_unique'
            );
            $table->index(['academic_year_id', 'class_id', 'section_id']);
            $table->index('teacher_id');
            $table->index('subject_id');
            $table->index('status');
        });

        // One active Class Teacher per AY/Class/Section; history retained.
        Schema::create('class_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->date('assigned_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();

            $table->index(['academic_year_id', 'class_id', 'section_id', 'is_active'], 'ct_active_idx');
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teachers');
        Schema::dropIfExists('teacher_subject_assignments');
    }
};
