<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Decouple asset assignment from business-module primary keys. Assignments
 * resolve through the Platform Identity Service (identity_id) for person targets
 * (e.g. staff), with the owner denormalized; non-person targets (department /
 * room / hostel / library / laboratory) use a decoupled target_reference string
 * — never a foreign key into another module's tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table): void {
            $table->unsignedBigInteger('identity_id')->nullable()->after('target_label');
            $table->string('owner_type')->nullable()->after('identity_id');
            $table->unsignedBigInteger('owner_id')->nullable()->after('owner_type');
            $table->string('target_reference')->nullable()->after('owner_id');

            $table->index('identity_id');
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table): void {
            $table->dropColumn(['identity_id', 'owner_type', 'owner_id', 'target_reference']);
        });
    }
};
