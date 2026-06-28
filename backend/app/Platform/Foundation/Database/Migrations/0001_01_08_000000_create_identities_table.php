<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform Identity registry — a permanent digital identity for every person in
 * the ERP (Student, Guardian, Staff, and future owner types). The single source
 * of truth for QR codes, barcodes and smart cards.
 *
 * Identity represents the PERSON, not their current role/placement: once issued,
 * identity_number / public_identifier / owner never change. Only the QR payload,
 * barcode value, status and metadata are (re)generated.
 *
 * No FK constraints (Platform infrastructure, owner is polymorphic) — only
 * indexes, tuned for fast lookup.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identities', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('identity_number');
            $table->string('identity_type');                 // student | guardian | staff | …
            $table->string('owner_type');                    // polymorphic owner model
            $table->unsignedBigInteger('owner_id');
            $table->string('public_identifier')->unique();   // opaque external reference (never a db id)
            $table->json('qr_payload')->nullable();
            $table->string('barcode_value')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('identity_number');
            $table->index('identity_type');
            $table->index('status');
            $table->index(['owner_type', 'owner_id']);
            $table->unique(['school_id', 'identity_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identities');
    }
};
