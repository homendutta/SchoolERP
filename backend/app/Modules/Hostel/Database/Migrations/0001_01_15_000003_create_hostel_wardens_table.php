<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Warden assignments (Staff → hostel). Wardens are always Staff members; the
 * assignment history is preserved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_wardens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('hostel_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('role')->default('chief'); // chief | assistant
            $table->date('assigned_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('hostel_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_wardens');
    }
};
