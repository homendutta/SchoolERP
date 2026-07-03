<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CMS-managed menus (header / footer / quick links, ordered + nestable), dynamic
 * public forms + their submissions, and admission enquiries. Submissions and
 * enquiries are captured in the ERP; the Communication Engine sends notifications.
 * Admission enquiries never auto-create an admission — they are enquiries only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('location'); // header / footer / quick_links
            $table->string('label');
            $table->string('url')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['school_id', 'location']);
        });

        Schema::create('cms_forms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // contact / admission_enquiry / general_enquiry
            $table->json('fields')->nullable(); // dynamic field definitions
            $table->string('notify_email')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'type']);
        });

        Schema::create('cms_form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('form_id')->nullable();
            $table->string('type')->default('contact');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index(['school_id', 'type']);
            $table->index('status');
        });

        Schema::create('cms_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('parent_name')->nullable();
            $table->string('student_name')->nullable();
            $table->string('interested_class')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_enquiries');
        Schema::dropIfExists('cms_form_submissions');
        Schema::dropIfExists('cms_forms');
        Schema::dropIfExists('cms_menus');
    }
};
