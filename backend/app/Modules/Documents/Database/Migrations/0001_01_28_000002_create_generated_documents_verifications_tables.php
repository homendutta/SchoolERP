<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generated documents (IMMUTABLE) and their verification log.
 *
 * A generated document stores an immutable rendered-HTML snapshot + the variable
 * snapshot + the template version it was produced from. It receives a platform
 * Identity (identity_id) whose identity_number/public_identifier drive QR + public
 * verification (QR images are generated dynamically, never stored). Regeneration
 * creates a NEW row (parent_id + incremented version); nothing is overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('document_number');
            $table->unsignedBigInteger('certificate_type_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedInteger('template_version')->default(1);
            $table->string('subject_type')->nullable(); // Student / Staff / Guardian
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('identity_id')->nullable(); // verification identity
            $table->string('verification_code')->nullable();
            $table->longText('rendered_html')->nullable();
            $table->json('variables')->nullable();
            $table->json('signatures')->nullable(); // Media ids for signatures
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable(); // previous version
            $table->string('status')->default('generated');
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->string('issued_to')->nullable();
            $table->date('issue_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'document_number']);
            $table->index('verification_code');
            $table->index(['subject_type', 'subject_id']);
            $table->index('certificate_type_id');
        });

        Schema::create('document_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->string('method'); // qr / document_number / code
            $table->string('result'); // valid / invalid / revoked
            $table->string('identifier')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
        Schema::dropIfExists('generated_documents');
    }
};
