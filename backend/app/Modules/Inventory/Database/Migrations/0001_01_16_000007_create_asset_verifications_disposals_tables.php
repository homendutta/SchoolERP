<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Physical verification history (verified / missing / damaged / disposed) and
 * disposals (sold / scrapped / donated / written off). Both are historical —
 * never overwritten; disposed assets are never deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_id');
            $table->string('status'); // verified | missing | damaged | disposed
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('asset_id');
            $table->index('status');
        });

        Schema::create('asset_disposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_id');
            $table->string('method'); // sold | scrapped | donated | written_off
            $table->text('reason')->nullable();
            $table->date('disposal_date')->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('asset_verifications');
    }
};
