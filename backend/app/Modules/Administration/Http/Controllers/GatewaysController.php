<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Actions\TestEmailGatewayAction;
use App\Modules\Administration\Models\EmailGateway;
use App\Modules\Administration\Models\PaymentGateway;
use App\Modules\Administration\Models\SmsGateway;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gateway configuration: Email (SMTP), SMS (provider), and Payment (Razorpay,
 * PhonePe, PayU, Cashfree) with test/live modes and enable/disable. Secrets are
 * encrypted at rest. No live processing here.
 */
class GatewaysController extends BaseController
{
    // ---- Email ----
    public function email(): JsonResponse
    {
        $gw = EmailGateway::query()->firstOrCreate(['id' => 1], ['provider' => 'smtp']);

        return $this->ok($this->emailPayload($gw));
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer'],
            'encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
            'from_address' => ['nullable', 'email'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'mode' => ['sometimes', 'in:test,live'],
            'is_enabled' => ['sometimes', 'boolean'],
        ]);

        $gw = EmailGateway::query()->firstOrCreate(['id' => 1], ['provider' => 'smtp']);
        $gw->update($data);

        return $this->ok($this->emailPayload($gw->refresh()), 'Email gateway saved.');
    }

    public function testEmail(Request $request): JsonResponse
    {
        $gw = EmailGateway::query()->firstOrCreate(['id' => 1], ['provider' => 'smtp']);
        $result = TestEmailGatewayAction::run($gw, $request->input('to'));

        return $this->ok($result, $result['message']);
    }

    // ---- SMS ----
    public function sms(): JsonResponse
    {
        $gw = SmsGateway::query()->firstOrCreate(['id' => 1], ['provider' => 'custom']);

        return $this->ok($this->smsPayload($gw));
    }

    public function updateSms(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider' => ['sometimes', 'string', 'max:64'],
            'api_url' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'sender_id' => ['nullable', 'string', 'max:32'],
            'mode' => ['sometimes', 'in:test,live'],
            'is_enabled' => ['sometimes', 'boolean'],
        ]);

        $gw = SmsGateway::query()->firstOrCreate(['id' => 1], ['provider' => 'custom']);
        $gw->update($data);

        return $this->ok($this->smsPayload($gw->refresh()), 'SMS gateway saved.');
    }

    public function testSms(Request $request): JsonResponse
    {
        $gw = SmsGateway::query()->firstOrCreate(['id' => 1], ['provider' => 'custom']);
        $ok = filled($gw->api_url) && filled($gw->api_key) && filled($gw->sender_id);

        return $this->ok(
            ['ok' => $ok, 'message' => $ok ? 'SMS configuration is valid.' : 'Missing api_url / api_key / sender_id.'],
            $ok ? 'SMS configuration is valid.' : 'SMS gateway is incomplete.',
        );
    }

    // ---- Payment ----
    public function payments(): JsonResponse
    {
        $existing = PaymentGateway::query()->get()->keyBy('provider');

        $list = collect(PaymentGateway::PROVIDERS)->map(function (string $provider) use ($existing) {
            $gw = $existing->get($provider);

            return [
                'provider' => $provider,
                'mode' => $gw?->mode?->value ?? 'test',
                'is_enabled' => (bool) ($gw?->is_enabled ?? false),
                'is_default' => (bool) ($gw?->is_default ?? false),
                'configured' => $gw !== null && filled($gw->key_id),
            ];
        });

        return $this->ok($list);
    }

    public function updatePayment(Request $request, string $provider): JsonResponse
    {
        $data = $request->validate([
            'provider' => ['sometimes'],
            'key_id' => ['nullable', 'string'],
            'key_secret' => ['nullable', 'string'],
            'mode' => ['sometimes', 'in:test,live'],
            'is_enabled' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        abort_unless(in_array($provider, PaymentGateway::PROVIDERS, true), 404);

        $gw = PaymentGateway::query()->updateOrCreate(['provider' => $provider], $data);

        return $this->ok([
            'provider' => $gw->provider,
            'mode' => $gw->mode?->value,
            'is_enabled' => $gw->is_enabled,
            'is_default' => $gw->is_default,
            'configured' => filled($gw->key_id),
        ], 'Payment gateway saved.');
    }

    /** @return array<string, mixed> */
    private function emailPayload(EmailGateway $gw): array
    {
        return [
            'provider' => $gw->provider, 'host' => $gw->host, 'port' => $gw->port,
            'encryption' => $gw->encryption, 'username' => $gw->username,
            'from_address' => $gw->from_address, 'from_name' => $gw->from_name,
            'mode' => $gw->mode?->value, 'is_enabled' => $gw->is_enabled,
            'has_password' => filled($gw->password),
        ];
    }

    /** @return array<string, mixed> */
    private function smsPayload(SmsGateway $gw): array
    {
        return [
            'provider' => $gw->provider, 'api_url' => $gw->api_url, 'sender_id' => $gw->sender_id,
            'mode' => $gw->mode?->value, 'is_enabled' => $gw->is_enabled, 'has_api_key' => filled($gw->api_key),
        ];
    }
}
