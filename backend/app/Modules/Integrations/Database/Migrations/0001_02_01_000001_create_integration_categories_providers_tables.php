<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Integration categories (Authentication, Communication, Payment, ... — nothing
 * hardcoded) and providers. A provider references a code-registered adapter; its
 * configuration is stored ENCRYPTED. Priority + is_default drive selection; health
 * is tracked from the last check.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['school_id', 'code']);
        });

        Schema::create('integration_providers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->string('code');          // matches a registered adapter code
            $table->string('adapter')->nullable();
            $table->string('version')->default('1.0');
            $table->string('status')->default('disabled'); // enabled / disabled
            $table->longText('config')->nullable(); // ENCRYPTED
            $table->string('health')->default('unknown');
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'category_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_providers');
        Schema::dropIfExists('integration_categories');
    }
};
