<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;

/**
 * Class Billing
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Billing extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'billing';

    /**
     * @param string $userId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getSubscription(string $userId): array
    {
        Validation::checkUuid($userId, 'userId');

        return $this->httpClient->get("user/{$userId}/subscription");
    }
}
