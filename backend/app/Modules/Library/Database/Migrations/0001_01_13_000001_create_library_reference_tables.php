<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Library reference data: publishers, authors, configurable categories, and the
 * physical storage layout (room / rack / shelf). Schools define their own —
 * nothing hardcoded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_publishers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });

        Schema::create('library_authors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });

        Schema::create('library_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
            $table->unique(['school_id', 'code']);
        });

        Schema::create('library_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('room')->nullable();
            $table->string('rack')->nullable();
            $table->string('shelf')->nullable();
            $table->string('position')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_locations');
        Schema::dropIfExists('library_categories');
        Schema::dropIfExists('library_authors');
        Schema::dropIfExists('library_publishers');
    }
};
