<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refunds and adjustments — both are INDEPENDENT records. Refunds never delete
 * payments; adjustments (credit/debit note, waiver, manual) never modify
 * historical payments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('transaction_number')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('type')->default('partial'); // full | partial
            $table->text('reason')->nullable();
            $table->date('refunded_on')->nullable();
            $table->string('status')->default('completed');
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('payment_id');
        });

        Schema::create('adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_fee_id')->nullable();
            $table->string('transaction_number')->nullable();
            $table->string('type')->default('manual'); // credit_note | debit_note | waiver | manual
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustments');
        Schema::dropIfExists('refunds');
    }
};
