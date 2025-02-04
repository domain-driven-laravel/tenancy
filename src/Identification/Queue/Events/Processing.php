<?php

declare(strict_types=1);

/*
 * This file is part of the tenancy/tenancy package.
 *
 * Copyright Tenancy for Laravel
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/tenancy
 */

namespace Tenancy\Identification\Drivers\Queue\Events;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Support\Arr;
use Tenancy\Identification\Contracts\Tenant;
use Tenancy\Identification\Drivers\Queue\Jobs\Job as TenancyJob;

class Processing
{
    use SerializesAndRestoresModelIdentifiers;

    public ?Tenant $tenant;

    public ?string $tenant_key;

    public ?string $tenant_identifier;

    public Job $job;

    public function __construct(
        public JobProcessing $event
    ) {
        $payload = $event->job->payload();
        $job = null;

        if ($command = Arr::get($payload, 'data.command')) {
            $job = $this->unserializeToJob($command);
        }

        $tenant = $job->getTenant();
        $tenant_key = $job->getTenantKey();
        $tenant_identifier = $job->getTenantIdentifier();

        $this->tenant = $tenant ?? null;
        $this->tenant_key = $tenant_key ?? $payload['tenant_key'] ?? null;
        $this->tenant_identifier = $tenant_identifier ?? $payload['tenant_identifier'] ?? null;

        $this->job = $command;
    }

    private function unserializeToJob(string $object): object
    {
        $stdClassObj = preg_replace('/^O:\d+:"[^"]++"/', 'O:'.strlen(TenancyJob::class).':"'.TenancyJob::class.'"', $object);

        return unserialize($stdClassObj, ['allowed_classes' => [TenancyJob::class]]);
    }
}
