<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student Withdrawals — withdrawing never deletes a student; it records the
 * withdrawal and moves the lifecycle status to Withdrawn.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('withdraw_date');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_withdrawals');
    }
};
