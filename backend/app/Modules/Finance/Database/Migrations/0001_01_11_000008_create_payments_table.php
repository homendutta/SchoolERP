<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments record what was PAID — a separate concept from what is owed (fees)
 * and from the accounting impact (ledger). Receipt & transaction numbers come
 * from the Number Generator. A payment is never deleted. Partial payments each
 * create their own transaction, and allocations record how a payment was split.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->string('receipt_number')->nullable();
            $table->string('transaction_number')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable(); // Master Data
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->date('paid_on')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('gateway')->nullable();
            $table->string('status')->default('completed');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('paid_on');
            $table->index('status');
            $table->unique(['school_id', 'receipt_number']);
        });

        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('student_fee_item_id')->nullable();
            $table->unsignedBigInteger('installment_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index('payment_id');
            $table->index('student_fee_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
