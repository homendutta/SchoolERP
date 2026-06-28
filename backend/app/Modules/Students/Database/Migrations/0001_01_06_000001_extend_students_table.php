<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student Management (Sprint 5) — extends the student identity with contact,
 * personal, address, medical and notes fields. Additive only; the original
 * students table migration (Admissions) is left untouched. Blood group is a
 * Master Data reference (blood_group_id), never hardcoded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            // Contact
            $table->string('phone')->nullable()->after('name');
            $table->string('email')->nullable()->after('phone');

            // Personal
            $table->string('religion')->nullable()->after('blood_group');
            $table->string('nationality')->nullable()->after('religion');
            $table->string('category')->nullable()->after('nationality');

            // Address
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');

            // Medical
            $table->unsignedBigInteger('blood_group_id')->nullable()->after('blood_group');
            $table->text('allergies')->nullable();
            $table->text('disabilities')->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('emergency_instructions')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->index('blood_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropIndex(['blood_group_id']);
            $table->dropColumn([
                'phone', 'email', 'religion', 'nationality', 'category',
                'city', 'state', 'postal_code', 'blood_group_id',
                'allergies', 'disabilities', 'medical_notes', 'emergency_instructions', 'notes',
            ]);
        });
    }
};
