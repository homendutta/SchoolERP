<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dedicated gateway tables per channel (instead of one generic table) and
 * feature flags. Secrets live in the encrypted `config`/credential columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('smtp');
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('encryption', 12)->nullable(); // tls|ssl|null
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // encrypted cast
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->string('mode')->default('test'); // test|live
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // e.g. twilio, msg91, custom
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable(); // encrypted cast
            $table->string('sender_id')->nullable();
            $table->json('config')->nullable();
            $table->string('mode')->default('test');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // razorpay|phonepe|payu|cashfree
            $table->text('key_id')->nullable();      // encrypted cast
            $table->text('key_secret')->nullable();  // encrypted cast
            $table->json('config')->nullable();
            $table->string('mode')->default('test'); // test|live
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique('provider');
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('sms_gateways');
        Schema::dropIfExists('email_gateways');
    }
};
