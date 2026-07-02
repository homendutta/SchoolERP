<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student transport assignments — a student belongs to a ROUTE and a STOP, never
 * directly to a vehicle. History is preserved: a re-assignment supersedes the
 * previous record (status changes) but is never deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_student_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('stop_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('shift')->nullable(); // morning | evening | both (null)
            $table->string('status')->default('active');
            $table->date('assigned_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('route_id');
            $table->index('stop_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_student_assignments');
    }
};
