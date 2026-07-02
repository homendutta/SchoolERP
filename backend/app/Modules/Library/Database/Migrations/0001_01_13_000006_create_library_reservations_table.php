<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reservation queues against a catalog title. Queue order is preserved by
 * queue_position; when a copy becomes available the front-of-queue reservation
 * is marked available and the borrower is notified via the Communication Engine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('identity_id');
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('book_id');
            $table->string('status')->default('pending');
            $table->unsignedInteger('queue_position')->default(1);
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('fulfilled_borrowing_id')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('book_id');
            $table->index('identity_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_reservations');
    }
};
