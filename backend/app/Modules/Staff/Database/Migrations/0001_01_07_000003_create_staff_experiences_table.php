<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff prior employment history — unlimited per employee. Experience certificate
 * is a Media reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('organization');
            $table->string('designation')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->foreignId('certificate_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamps();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_experiences');
    }
};
