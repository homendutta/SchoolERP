<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guardian photo as a Media reference (shared Media pipeline) — never a path.
 * Additive migration; the original guardians table migration is untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardians', function (Blueprint $table): void {
            $table->unsignedBigInteger('photo_media_id')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table): void {
            $table->dropColumn('photo_media_id');
        });
    }
};
