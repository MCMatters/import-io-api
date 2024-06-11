<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

class Billing extends Endpoint
{
    protected string $subDomain = 'billing';

    public function getSubscription(string $userId): array
    {
        return $this->httpClient->get("user/{$userId}/subscription");
    }
}
