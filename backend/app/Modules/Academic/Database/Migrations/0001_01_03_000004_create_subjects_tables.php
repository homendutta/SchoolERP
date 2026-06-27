<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            // Subject Type comes from Master Data (master_data_values), never hardcoded.
            $table->foreignId('subject_type_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('slug');
            $table->boolean('theory')->default(true);
            $table->boolean('practical')->default(false);
            $table->unsignedInteger('credits')->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug']);
            $table->unique(['school_id', 'code']);
            $table->index('status');
            $table->index('subject_type_id');
            $table->index('created_at');
        });

        Schema::create('subject_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug']);
            $table->index('status');
        });

        Schema::create('subject_group_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_group_id')->constrained('subject_groups')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['subject_group_id', 'subject_id']);
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_group_subjects');
        Schema::dropIfExists('subject_groups');
        Schema::dropIfExists('subjects');
    }
};
