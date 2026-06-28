<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff reference their permanent platform Identity (additive).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table): void {
            $table->unsignedBigInteger('identity_id')->nullable()->after('uuid');
            $table->index('identity_id');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table): void {
            $table->dropIndex(['identity_id']);
            $table->dropColumn('identity_id');
        });
    }
};
