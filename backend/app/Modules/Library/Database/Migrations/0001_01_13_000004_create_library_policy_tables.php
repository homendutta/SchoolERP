<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable circulation policy: per-borrower borrow periods, renewal limits,
 * reservation expiry (library_settings), and configurable fine rules (Library
 * calculates fines; Finance collects payment).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedInteger('student_borrow_days')->default(14);
            $table->unsignedInteger('staff_borrow_days')->default(30);
            $table->unsignedInteger('max_renewals')->default(2);
            $table->unsignedInteger('max_books_per_borrower')->default(3);
            $table->unsignedInteger('reservation_expiry_days')->default(3);
            $table->timestamps();

            $table->unique('school_id');
        });

        Schema::create('library_fine_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('borrower_type')->nullable(); // null = all; else Student/Staff class
            $table->string('mode')->default('daily');    // daily | flat
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('grace_period_days')->default(0);
            $table->decimal('max_fine', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_fine_rules');
        Schema::dropIfExists('library_settings');
    }
};
