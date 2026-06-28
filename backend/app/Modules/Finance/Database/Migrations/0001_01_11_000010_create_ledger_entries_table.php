<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Financial Ledger — records the ACCOUNTING impact of payments, refunds and
 * adjustments. Ledger entries are generated automatically and remain INDEPENDENT
 * of payments (the ledger never modifies a payment). The polymorphic source
 * links back to the originating transaction for audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('entry_type'); // debit | credit
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('narration')->nullable();
            $table->date('entry_date')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index(['source_type', 'source_id']);
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
