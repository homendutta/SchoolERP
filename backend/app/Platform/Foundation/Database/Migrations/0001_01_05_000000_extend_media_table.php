<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the Media Library for the shared Media Upload Pipeline: identity
 * (uuid, original/stored filename, extension), media attributes (width, height,
 * duration, checksum), visibility, school scoping and a metadata blob (which
 * holds the per-size thumbnail map). Additive only — the original create_media
 * migration is left untouched so migration history is preserved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->after('id');
            $table->unsignedBigInteger('school_id')->nullable()->after('uuid');
            $table->string('visibility')->default('private')->after('disk');
            $table->string('original_filename')->nullable()->after('path');
            $table->string('stored_filename')->nullable()->after('original_filename');
            $table->string('extension', 20)->nullable()->after('stored_filename');
            $table->unsignedInteger('width')->nullable()->after('size');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->unsignedInteger('duration')->nullable()->after('height');
            $table->string('checksum', 64)->nullable()->after('duration');
            $table->json('metadata')->nullable()->after('checksum');

            $table->unique('uuid');
            $table->index('school_id');
            $table->index('collection');
            $table->index('disk');
            $table->index('visibility');
            $table->index('checksum');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropUnique(['uuid']);
            $table->dropIndex(['school_id']);
            $table->dropIndex(['collection']);
            $table->dropIndex(['disk']);
            $table->dropIndex(['visibility']);
            $table->dropIndex(['checksum']);
            $table->dropIndex(['created_at']);
            $table->dropColumn([
                'uuid', 'school_id', 'visibility', 'original_filename', 'stored_filename',
                'extension', 'width', 'height', 'duration', 'checksum', 'metadata',
            ]);
        });
    }
};
