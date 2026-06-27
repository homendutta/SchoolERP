<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Focused one-to-one school settings tables. Branding stores media references
 * (FK to the platform `media` table), not raw path strings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_branding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('theme_color', 16)->default('#001F3F');
            foreach ([
                'logo_media_id', 'logo_dark_media_id', 'favicon_media_id',
                'login_background_media_id', 'principal_signature_media_id',
                'stamp_media_id', 'report_logo_media_id', 'receipt_logo_media_id',
                'id_card_media_id',
            ] as $col) {
                $table->foreignId($col)->nullable()->constrained('media')->nullOnDelete();
            }
            $table->timestamps();
            $table->unique('school_id');
        });

        Schema::create('school_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->timestamps();
            $table->unique('school_id');
        });

        Schema::create('school_regional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('timezone')->default('UTC');
            $table->string('currency', 8)->default('INR');
            $table->string('locale', 8)->default('en');
            $table->string('date_format', 20)->default('Y-m-d');
            $table->string('time_format', 12)->default('H:i');
            $table->string('week_start', 12)->default('monday');
            $table->timestamps();
            $table->unique('school_id');
        });

        Schema::create('school_academic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('academic_year')->nullable();
            $table->unsignedTinyInteger('academic_year_start_month')->default(4);
            $table->string('session_label')->nullable();
            $table->timestamps();
            $table->unique('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_academic_settings');
        Schema::dropIfExists('school_regional');
        Schema::dropIfExists('school_contact');
        Schema::dropIfExists('school_branding');
    }
};
