<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The catalog. One entry represents one publication and may have many physical
 * copies. A catalog record is NEVER borrowed. Authors are many-to-many (never
 * comma-separated). Cover image is a Media Platform reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('isbn')->nullable();
            $table->string('edition')->nullable();
            $table->string('language')->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('isbn');
            $table->index('publisher_id');
            $table->index('category_id');
        });

        Schema::create('library_book_author', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('author_id');
            $table->timestamps();

            $table->unique(['book_id', 'author_id']);
            $table->index('author_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_book_author');
        Schema::dropIfExists('library_books');
    }
};
