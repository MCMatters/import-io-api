<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Http\Client;
use McMatters\ImportIo\Utilities\Retry;

use const null;

abstract class Endpoint
{
    protected string $subDomain;

    protected Client $httpClient;

    public function __construct(
        string $apiKey,
        ?Retry $retry = null,
        array $httpClientOptions = [],
    ) {
        $this->httpClient = new Client(
            $this->subDomain,
            $apiKey,
            $retry,
            $httpClientOptions,
        );
    }
}
