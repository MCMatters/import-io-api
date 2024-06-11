<?php

declare(strict_types=1);

namespace McMatters\ImportIo;

use McMatters\ImportIo\Endpoints\Api;
use McMatters\ImportIo\Endpoints\Billing;
use McMatters\ImportIo\Endpoints\Data;
use McMatters\ImportIo\Endpoints\Extraction;
use McMatters\ImportIo\Endpoints\Rss;
use McMatters\ImportIo\Endpoints\Run;
use McMatters\ImportIo\Endpoints\Schedule;
use McMatters\ImportIo\Endpoints\Store;
use McMatters\ImportIo\Utilities\Retry;

use const null;

class ImportIoClient
{
    protected array $endpoints = [];

    public function __construct(
        protected string $apiKey,
        protected ?Retry $retry = null,
        protected array $httpClientOptions = [],
    ) {
    }

    public function api(): Api
    {
        return $this->endpoint(Api::class);
    }

    public function billing(): Billing
    {
        return $this->endpoint(Billing::class);
    }

    public function data(): Data
    {
        return $this->endpoint(Data::class);
    }

    public function extraction(): Extraction
    {
        return $this->endpoint(Extraction::class);
    }

    public function rss(): Rss
    {
        return $this->endpoint(Rss::class);
    }

    public function run(): Run
    {
        return $this->endpoint(Run::class);
    }

    public function schedule(): Schedule
    {
        return $this->endpoint(Schedule::class);
    }

    public function store(): Store
    {
        return $this->endpoint(Store::class);
    }

    protected function endpoint(string $class)
    {
        if (!isset($this->endpoints[$class])) {
            $this->endpoints[$class] = new $class(
                $this->apiKey,
                $this->retry,
                $this->httpClientOptions,
            );
        }

        return $this->endpoints[$class];
    }
}
