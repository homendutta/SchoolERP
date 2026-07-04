<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Adapters;

use App\Modules\Integrations\Services\RestConnector;
use App\Modules\Integrations\Support\IntegrationProvider;

/**
 * A generic REST provider adapter — the reference implementation for any HTTP-API
 * integration (webhooks, cloud, government, AI, analytics …). It performs its
 * health/test through the reusable REST connector; concrete providers subclass or
 * register their own adapter with real endpoints.
 */
class RestConnectorAdapter implements IntegrationProvider
{
    public function __construct(private readonly RestConnector $connector) {}

    public function code(): string
    {
        return 'rest';
    }

    public function category(): string
    {
        return 'rest';
    }

    public function name(): string
    {
        return 'Generic REST Connector';
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{status:string, detail?:string}
     */
    public function healthCheck(array $config): array
    {
        $url = $config['health_url'] ?? ($config['base_url'] ?? null);
        if ($url === null) {
            return ['status' => 'unknown', 'detail' => 'No base_url configured.'];
        }

        $result = $this->connector->request('GET', (string) $url, [
            'school_id' => $config['school_id'] ?? null,
            'provider_code' => 'rest',
            'timeout' => 8,
        ]);

        return ['status' => $result['ok'] ? 'healthy' : 'down', 'detail' => 'HTTP '.$result['status']];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function test(array $config): array
    {
        return $this->healthCheck($config);
    }
}
