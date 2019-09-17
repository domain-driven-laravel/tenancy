<?php

namespace Tenancy\Identification\Drivers\Queue\Middleware;

use Tenancy\Environment;

class SaveTenantOnQueuePayload
{
    public function __invoke(string $connection, string $queue = null, array $payload = [])
    {
        if (isset($payload['tenant_key'], $payload['tenant_identifier'])) {
            return [];
        }

        /** @var Environment $environment */
        $environment = resolve(Environment::class);
        $tenant      = $environment->getTenant();

        return $tenant ? [
            'tenant_key'        => $tenant->getTenantKey(),
            'tenant_identifier' => $tenant->getTenantIdentifier(),
        ] : [];
    }
}
