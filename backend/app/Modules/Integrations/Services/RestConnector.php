<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Enums\LogStatus;
use App\Modules\Integrations\Models\IntegrationLog;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * The reusable REST connector every integration uses to call an external HTTP API
 * (GET/POST/PUT/PATCH/DELETE) with configurable timeout, retries, headers and
 * authentication. Every request — success or failure — is logged. No business
 * module builds its own HTTP client.
 */
class RestConnector
{
    /**
     * @param  array<string, mixed>  $options  {headers?:array, json?:array, query?:array, token?:string, timeout?:int, retries?:int, provider_id?:int, provider_code?:string, school_id?:int}
     * @return array{ok:bool, status:int, body:mixed, duration_ms:int}
     */
    public function request(string $method, string $url, array $options = []): array
    {
        $timeout = (int) ($options['timeout'] ?? 15);
        $retries = (int) ($options['retries'] ?? 2);
        $started = microtime(true);

        $client = Http::timeout($timeout)->retry(max(1, $retries), 200, throw: false);
        if (! empty($options['headers']) && is_array($options['headers'])) {
            $client = $client->withHeaders($options['headers']);
        }
        if (! empty($options['token'])) {
            $client = $client->withToken((string) $options['token']);
        }

        try {
            $response = $client->send(strtoupper($method), $url, array_filter([
                'json' => $options['json'] ?? null,
                'query' => $options['query'] ?? null,
            ], fn ($v) => $v !== null));

            $duration = (int) round((microtime(true) - $started) * 1000);
            $this->log($options, $method, $url, $response->successful() ? LogStatus::Success : LogStatus::Failure, $response->status(), $duration, $response->successful() ? null : 'HTTP '.$response->status());

            return ['ok' => $response->successful(), 'status' => $response->status(), 'body' => $this->body($response), 'duration_ms' => $duration];
        } catch (ConnectionException $e) {
            $duration = (int) round((microtime(true) - $started) * 1000);
            $this->log($options, $method, $url, LogStatus::Failure, 0, $duration, $e->getMessage());

            return ['ok' => false, 'status' => 0, 'body' => null, 'duration_ms' => $duration];
        }
    }

    private function body(Response $response): mixed
    {
        $json = $response->json();

        return $json ?? $response->body();
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function log(array $options, string $method, string $url, LogStatus $status, int $code, int $duration, ?string $error): void
    {
        IntegrationLog::query()->create([
            'school_id' => $options['school_id'] ?? null,
            'provider_id' => $options['provider_id'] ?? null,
            'provider_code' => $options['provider_code'] ?? null,
            'method' => strtoupper($method),
            'url' => $url,
            'status' => $status->value,
            'response_code' => $code,
            'duration_ms' => $duration,
            'error' => $error,
            'created_at' => now(),
        ]);
    }
}
