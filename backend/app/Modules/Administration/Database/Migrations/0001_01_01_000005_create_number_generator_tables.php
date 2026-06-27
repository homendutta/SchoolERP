<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Configurable sequence definitions per number type.
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('key'); // admission_number, staff_number, guardian_number, receipt_number, invoice_number, asset_number
            $table->string('label')->nullable();
            $table->unsignedBigInteger('initial_number')->default(1);
            $table->unsignedBigInteger('current_number')->default(0); // last issued
            $table->unsignedBigInteger('maximum_number')->nullable();
            $table->string('prefix')->default('');
            $table->string('suffix')->default('');
            $table->unsignedInteger('padding')->default(0);
            $table->unsignedInteger('increment')->default(1);
            $table->boolean('manual_entry_allowed')->default(false);
            $table->string('format')->default('{prefix}{number}{suffix}');
            $table->string('reset_policy')->default('none'); // none|daily|monthly|yearly
            $table->string('last_reset_period')->nullable();  // internal boundary key
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'key']);
        });

        // Business Number Registry — every issued number, for uniqueness + history.
        Schema::create('business_number_registry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('type'); // matches number_sequences.key
            $table->string('number');
            $table->foreignId('sequence_id')->nullable()->constrained('number_sequences')->nullOnDelete();
            $table->foreignId('issued_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'type', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_number_registry');
        Schema::dropIfExists('number_sequences');
    }
};
