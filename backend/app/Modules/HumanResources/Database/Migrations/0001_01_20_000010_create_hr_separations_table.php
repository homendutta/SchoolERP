<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee separation (resignation / retirement / termination / contract
 * completion / death). Employees are NEVER deleted — a separation records the
 * exit and closes the current employment while creating a new "separated"
 * employment state. Separated employees remain fully searchable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_separations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('separation_type');
            $table->date('last_working_day')->nullable();
            $table->text('reason')->nullable();
            $table->string('clearance_status')->default('pending');
            $table->text('exit_notes')->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('separation_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_separations');
    }
};
