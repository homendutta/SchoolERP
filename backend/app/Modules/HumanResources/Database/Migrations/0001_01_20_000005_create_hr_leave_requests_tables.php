<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leave requests, their multi-level approval trail, and per-employee balances.
 * Leave is processed ONLY through the Leave Engine (apply / approve / reject /
 * cancel), which writes the approval trail, the balance, the Timeline, the Audit
 * Log and a Communication event. The approval trail is append-only history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->unsignedBigInteger('leave_policy_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 6, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('approval_levels')->default(1);
            $table->unsignedInteger('current_level')->default(0);
            $table->date('applied_on')->nullable();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->date('decided_on')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('leave_type_id');
            $table->index('status');
        });

        Schema::create('hr_leave_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('hr_leave_requests')->cascadeOnDelete();
            $table->unsignedInteger('level');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('action'); // approved / rejected
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index('leave_request_id');
        });

        Schema::create('hr_leave_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->unsignedInteger('year');
            $table->decimal('allocated', 8, 2)->default(0);
            $table->decimal('carried_forward', 8, 2)->default(0);
            $table->decimal('used', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['staff_id', 'leave_type_id', 'year']);
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_leave_balances');
        Schema::dropIfExists('hr_leave_approvals');
        Schema::dropIfExists('hr_leave_requests');
    }
};
