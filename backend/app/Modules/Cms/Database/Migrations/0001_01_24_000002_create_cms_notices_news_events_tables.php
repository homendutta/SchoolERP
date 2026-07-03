<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notice board, news and events. All are publishable content (draft / published /
 * scheduled / archived). Attachments and images are Media references. The public
 * site always shows current (published, in-window) items only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_notices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->date('publish_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('priority')->default('normal');
            $table->boolean('featured')->default(false);
            $table->unsignedBigInteger('attachment_media_id')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index('expiry_date');
        });

        Schema::create('cms_news', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->longText('body')->nullable();
            $table->string('excerpt')->nullable();
            $table->unsignedBigInteger('featured_image_media_id')->nullable();
            $table->json('gallery')->nullable(); // array of media ids
            $table->json('seo')->nullable();
            $table->date('publish_date')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('status')->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('cms_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->date('event_date')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('venue')->nullable();
            $table->unsignedBigInteger('featured_image_media_id')->nullable();
            $table->boolean('registration_required')->default(false);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index('event_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_events');
        Schema::dropIfExists('cms_news');
        Schema::dropIfExists('cms_notices');
    }
};
