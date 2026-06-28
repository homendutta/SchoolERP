<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Student ↔ Guardian relationship lives on the pivot, not on the Guardian.
 * Relationship type is Master Data (relationship_type_id) — never free text.
 * Additive migration; the original pivot migration is untouched (the obsolete
 * free-text `relation` column is dropped here).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_guardian', function (Blueprint $table): void {
            $table->unsignedBigInteger('relationship_type_id')->nullable()->after('guardian_id');
            $table->boolean('emergency_contact')->default(false)->after('is_primary');
            $table->boolean('pickup_authorized')->default(false)->after('emergency_contact');
            $table->boolean('financial_responsible')->default(false)->after('pickup_authorized');
            $table->text('notes')->nullable()->after('financial_responsible');

            $table->index('relationship_type_id');
        });

        Schema::table('student_guardian', function (Blueprint $table): void {
            $table->dropColumn('relation');
        });
    }

    public function down(): void
    {
        Schema::table('student_guardian', function (Blueprint $table): void {
            $table->string('relation')->nullable()->after('guardian_id');
            $table->dropIndex(['relationship_type_id']);
            $table->dropColumn([
                'relationship_type_id', 'emergency_contact', 'pickup_authorized',
                'financial_responsible', 'notes',
            ]);
        });
    }
};
