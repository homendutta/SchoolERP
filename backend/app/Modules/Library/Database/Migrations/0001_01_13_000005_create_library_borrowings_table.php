<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Borrowing transactions (against a physical copy, never the catalog). The
 * borrower is resolved through the Identity Platform (identity_id), with owner
 * denormalized for fast queries. Returns create their own values on the record;
 * the borrowing row's identity/copy is never repurposed. Renewals keep history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_borrowings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('identity_id');
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('copy_id');
            $table->unsignedBigInteger('book_id');
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->string('status')->default('borrowed');
            $table->unsignedInteger('renewals_count')->default(0);
            $table->unsignedInteger('late_days')->default(0);
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->boolean('fine_waived')->default(false);
            $table->text('damage_notes')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->unsignedBigInteger('returned_to')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('identity_id');
            $table->index(['owner_type', 'owner_id']);
            $table->index('copy_id');
            $table->index('book_id');
            $table->index('status');
            $table->index('due_date');
        });

        Schema::create('library_renewals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('borrowing_id');
            $table->date('renewed_on');
            $table->date('previous_due_date');
            $table->date('new_due_date');
            $table->unsignedBigInteger('renewed_by')->nullable();
            $table->timestamps();

            $table->index('borrowing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_renewals');
        Schema::dropIfExists('library_borrowings');
    }
};
