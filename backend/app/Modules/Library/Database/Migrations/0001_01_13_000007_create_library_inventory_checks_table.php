<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory verification history. Each check records a copy's audited state
 * (verified / missing / misplaced / damaged). Immutable audit — copies are never
 * deleted; their history is preserved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_inventory_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('copy_id');
            $table->string('status'); // verified | missing | misplaced | damaged
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('copy_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_inventory_checks');
    }
};
