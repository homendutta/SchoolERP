<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photo galleries (albums + images), video gallery (external refs or Media) and
 * downloads. Images and files are Media references — Media storage is never
 * duplicated; videos store only a provider + reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_galleries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });

        Schema::create('cms_gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_id')->constrained('cms_galleries')->cascadeOnDelete();
            $table->unsignedBigInteger('media_id');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();

            $table->index('gallery_id');
        });

        Schema::create('cms_videos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('provider')->default('youtube'); // youtube / vimeo / self_hosted
            $table->string('video_url')->nullable();
            $table->unsignedBigInteger('media_id')->nullable(); // for self_hosted
            $table->unsignedBigInteger('thumbnail_media_id')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });

        Schema::create('cms_downloads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_downloads');
        Schema::dropIfExists('cms_videos');
        Schema::dropIfExists('cms_gallery_images');
        Schema::dropIfExists('cms_galleries');
    }
};
