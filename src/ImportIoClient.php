<?php

declare(strict_types=1);

namespace McMatters\ImportIo;

use McMatters\ImportIo\Endpoints\{
    Api, Data, Extraction, Rss, Run, Schedule, Store
};
use McMatters\ImportIo\Utilities\Retry;

use const null;

/**
 * Class ImportIoClient
 *
 * @package McMatters\ImportIo
 */
class ImportIoClient
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var \McMatters\ImportIo\Utilities\Retry|null
     */
    protected $retry;

    /**
     * @var array
     */
    protected $endpoints = [];

    /**
     * ImportIo constructor.
     *
     * @param string $apiKey
     * @param \McMatters\ImportIo\Utilities\Retry|null $retry
     */
    public function __construct(string $apiKey, Retry $retry = null)
    {
        $this->apiKey = $apiKey;
        $this->retry = $retry;
    }

    /**
     * @return \McMatters\ImportIo\Endpoints\Api
     */
    public function api(): Api
    {
        return $this->endpoint(Api::class);
    }

    /**
     * @return \McMatters\ImportIo\Endpoints\Data
     */
    public function data(): Data
    {
        return $this->endpoint(Data::class);
    }

    /**
     * @return \McMatters\ImportIo\Endpoints\Extraction
     */
    public function extraction(): Extraction
    {
        return $this->endpoint(Extraction::class);
    }

    /**
     * @return \McMatters\ImportIo\Endpoints\Rss
     */
    public function rss(): Rss
    {
        return $this->endpoint(Rss::class);
    }

    /**
     * @return \McMatters\ImportIo\Endpoints\Run
     */
    public function run(): Run
    {
        return $this->endpoint(Run::class);
    }

    /**
     * @return \McMatters\ImportIo\Endpoints\Schedule
     */
    public function schedule(): Schedule
    {
        return $this->endpoint(Schedule::class);
    }

    /**
     * @return \McMatters\ImportIo\Endpoints\Store
     */
    public function store(): Store
    {
        return $this->endpoint(Store::class);
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    protected function endpoint(string $class)
    {
        if (!isset($this->endpoints[$class])) {
            $this->endpoints[$class] = new $class($this->apiKey, $this->retry);
        }

        return $this->endpoints[$class];
    }
}
