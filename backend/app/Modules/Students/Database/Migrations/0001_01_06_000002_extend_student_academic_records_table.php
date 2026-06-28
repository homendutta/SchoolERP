<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the immutable-history fields to the per-year academic record: the
 * admission number snapshot and the link to the record a promotion came from.
 * History is never updated — promotion always inserts a new row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_academic_records', function (Blueprint $table): void {
            $table->string('admission_number')->nullable()->after('section_id');
            $table->unsignedBigInteger('promoted_from_record_id')->nullable()->after('admission_number');

            $table->index('promoted_from_record_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_academic_records', function (Blueprint $table): void {
            $table->dropIndex(['promoted_from_record_id']);
            $table->dropColumn(['admission_number', 'promoted_from_record_id']);
        });
    }
};
