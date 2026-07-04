<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Support;

/**
 * The common contract EVERY integration provider implements. Business modules
 * never call third-party APIs directly — they go through the Integration Platform,
 * which selects a provider that fulfils this interface. Concrete provider SDKs
 * (Razorpay, Twilio, S3, Zoom, OpenAI, DigiLocker …) are added as adapters WITHOUT
 * changing the platform.
 */
interface IntegrationProvider
{
    /** Unique adapter code (e.g. 'manual', 'razorpay', 'twilio'). */
    public function code(): string;

    /** The category this provider belongs to (e.g. 'payment', 'communication'). */
    public function category(): string;

    /** A human display name. */
    public function name(): string;

    /**
     * Report the provider's health (no side effects on business data).
     *
     * @param  array<string, mixed>  $config
     * @return array{status:string, detail?:string}
     */
    public function healthCheck(array $config): array;

    /**
     * Perform a lightweight connectivity/config test.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function test(array $config): array;
}
