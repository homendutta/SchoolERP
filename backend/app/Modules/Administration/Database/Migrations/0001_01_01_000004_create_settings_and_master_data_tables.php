<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Settings + Master Data Engine (final structure):
 *   master_data_groups -> master_data_types -> master_data_values
 * Values support archive/restore via soft deletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('group')->default('general');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string|int|bool|json
            $table->timestamps();
            $table->unique(['school_id', 'group', 'key']);
        });

        Schema::create('master_data_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('master_data_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('master_data_groups')->nullOnDelete();
            $table->string('slug')->unique(); // blood_group, departments, designations...
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('master_data_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('master_data_types')->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['type_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_values');
        Schema::dropIfExists('master_data_types');
        Schema::dropIfExists('master_data_groups');
        Schema::dropIfExists('settings');
    }
};
