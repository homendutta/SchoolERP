<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable admission approval workflow.
 *
 * admission_workflow_steps  — the school's step DEFINITION (Reception → … →
 *                             Approved). One-step or multi-step; never hardcoded.
 * admission_approval_steps  — a per-application INSTANCE of those steps, each
 *                             with its own status/actor/remarks (audit trail).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_workflow_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('role_slug')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
            $table->index('is_active');
            $table->index(['school_id', 'sort_order']);
        });

        Schema::create('admission_approval_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->nullable()->constrained('admission_workflow_steps')->nullOnDelete();
            $table->string('name');
            $table->string('role_slug')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('application_id');
            $table->index('status');
            $table->index(['application_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_approval_steps');
        Schema::dropIfExists('admission_workflow_steps');
    }
};
