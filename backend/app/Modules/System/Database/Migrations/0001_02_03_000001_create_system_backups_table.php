<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backup manifests (metadata + restore metadata). The module records WHAT was
 * backed up (database tables + row counts / media / config) and where — cloud
 * backup providers are intentionally out of scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_backups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('type')->default('full');
            $table->string('status')->default('pending');
            $table->string('path')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->json('manifest')->nullable();
            $table->string('checksum')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};
