<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unlimited installment schedule for a student fee. Different fee structures can
 * carry different installment schedules.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_installments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_fee_id');
            $table->string('name');
            $table->date('due_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_fee_id');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_installments');
    }
};
