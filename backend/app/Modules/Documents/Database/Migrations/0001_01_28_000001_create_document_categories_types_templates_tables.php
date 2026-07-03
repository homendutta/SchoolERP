<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable document categories, certificate types and VERSIONED templates.
 *
 * Nothing is hardcoded — categories and certificate types are master data. A
 * template is versioned: a new version is a NEW row that links to the previous
 * via parent_id (old versions remain available). Logos/watermarks/backgrounds are
 * Media references.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });

        Schema::create('document_certificate_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedBigInteger('default_template_id')->nullable();
            $table->string('subject_kind')->default('student'); // student / staff / guardian
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'category_id']);
        });

        Schema::create('document_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('certificate_type_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable(); // previous version
            $table->longText('html')->nullable();
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->json('variables')->nullable();
            $table->unsignedBigInteger('logo_media_id')->nullable();
            $table->unsignedBigInteger('watermark_media_id')->nullable();
            $table->unsignedBigInteger('background_media_id')->nullable();
            $table->json('margins')->nullable();
            $table->string('orientation')->default('portrait');
            $table->string('paper_size')->default('a4');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'certificate_type_id']);
            $table->index(['code', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_certificate_types');
        Schema::dropIfExists('document_categories');
    }
};
