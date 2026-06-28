<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable discounts, scholarships (independent of discounts), sibling rules
 * and fine rules. All are school-defined — nothing hardcoded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('method')->default('percentage'); // percentage | fixed
            $table->decimal('value', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });

        Schema::create('scholarships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type')->default('partial'); // full | partial
            $table->string('method')->default('percentage');
            $table->decimal('value', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });

        Schema::create('sibling_discount_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('child_order')->default(2); // 2nd child, 3rd child…
            $table->string('method')->default('percentage');
            $table->decimal('value', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });

        Schema::create('fine_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('fee_category_id')->nullable();
            $table->string('mode')->default('flat'); // daily | weekly | monthly | flat
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('grace_period_days')->default(0);
            $table->decimal('max_fine', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_rules');
        Schema::dropIfExists('sibling_discount_rules');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('discounts');
    }
};
