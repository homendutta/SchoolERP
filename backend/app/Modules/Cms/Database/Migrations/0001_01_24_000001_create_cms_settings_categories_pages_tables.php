<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website settings (one row per school — the public site's global config),
 * content categories (shared across notices/news/gallery/videos/downloads) and
 * CMS pages (arbitrary static pages made dynamic, with per-page SEO). All images
 * are Media references; nothing is hardcoded on the public site.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('site_name')->nullable();
            $table->unsignedBigInteger('logo_media_id')->nullable();
            $table->unsignedBigInteger('favicon_media_id')->nullable();
            $table->json('theme_colors')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->json('social_links')->nullable();
            $table->text('footer')->nullable();
            $table->string('copyright')->nullable();
            $table->text('google_map')->nullable();
            $table->json('homepage_config')->nullable();
            $table->timestamps();

            $table->unique('school_id');
        });

        Schema::create('cms_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('type'); // notice / news / gallery / video / download
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'type']);
        });

        Schema::create('cms_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('body')->nullable();
            $table->string('template')->nullable();
            $table->json('seo')->nullable(); // meta_title/description/keywords/og/canonical/robots
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('cms_categories');
        Schema::dropIfExists('cms_settings');
    }
};
