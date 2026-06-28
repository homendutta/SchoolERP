<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A published communication request from a business module → fans out into many
 * per-recipient messages. The source/event records WHICH module/hook published
 * it (business modules never send directly).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('source')->nullable(); // e.g. 'finance', 'manual'
            $table->string('event')->nullable();  // e.g. 'fee_due'
            $table->string('channel');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('audience_type')->default('custom');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_batches');
    }
};
