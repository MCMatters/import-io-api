<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Http\Client;

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
     */
    public function __construct(string $apiKey)
    {
        $this->httpClient = new Client($this->subDomain, $apiKey);
    }
}
