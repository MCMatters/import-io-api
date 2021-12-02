<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Http\Client;
use McMatters\ImportIo\Utilities\Retry;

use const null;

/**
 * Class Endpoint
 *
 * @package McMatters\ImportIo\Endpoints
 */
abstract class Endpoint
{
    /**
     * @var string
     */
    protected $subDomain;

    /**
     * @var \McMatters\ImportIo\Http\Client
     */
    protected $httpClient;

    /**
     * Endpoint constructor.
     *
     * @param string $apiKey
     * @param \McMatters\ImportIo\Utilities\Retry|null $retry
     * @param array $httpClientOptions
     */
    public function __construct(
        string $apiKey,
        Retry $retry = null,
        array $httpClientOptions = []
    ) {
        $this->httpClient = new Client(
            $this->subDomain,
            $apiKey,
            $retry,
            $httpClientOptions
        );
    }
}
