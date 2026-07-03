<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable classroom resources and classroom discussions (with threaded posts).
 * Teachers create/moderate discussions; students reply. There is NO private
 * messaging — discussions are classroom-scoped; notifications reuse Communication.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->string('topic')->nullable();
            $table->string('type')->nullable(); // notes / worksheet / reading_list / ...
            $table->text('body')->nullable();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'subject_id']);
        });

        Schema::create('lms_discussions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->text('body')->nullable();
            $table->boolean('locked')->default(false);
            $table->string('status')->default('published');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'subject_id']);
        });

        Schema::create('lms_discussion_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('discussion_id')->constrained('lms_discussions')->cascadeOnDelete();
            $table->string('author_type'); // Student or User (teacher)
            $table->unsignedBigInteger('author_id');
            $table->text('body');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('status')->default('visible'); // visible / hidden (moderation)
            $table->timestamps();

            $table->index('discussion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_discussion_posts');
        Schema::dropIfExists('lms_discussions');
        Schema::dropIfExists('lms_resources');
    }
};
