<?php

declare(strict_types = 1);

namespace McMatters\ImportIo;

use McMatters\ImportIo\Endpoints\{
    Api, Data, Extraction, Rss, Run, Schedule, Store
};
use function ucfirst;

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
     * @var array
     */
    protected $endpoints = [];

    /**
     * ImportIo constructor.
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return Api
     */
    public function api(): Api
    {
        return $this->endpoint(__FUNCTION__);
    }

    /**
     * @return Data
     */
    public function data(): Data
    {
        return $this->endpoint(__FUNCTION__);
    }

    /**
     * @return Extraction
     */
    public function extraction(): Extraction
    {
        return $this->endpoint(__FUNCTION__);
    }

    /**
     * @return Rss
     */
    public function rss(): Rss
    {
        return $this->endpoint(__FUNCTION__);
    }

    /**
     * @return Run
     */
    public function run(): Run
    {
        return $this->endpoint(__FUNCTION__);
    }

    /**
     * @return Schedule
     */
    public function schedule(): Schedule
    {
        return $this->endpoint(__FUNCTION__);
    }

    /**
     * @return Store
     */
    public function store(): Store
    {
        return $this->endpoint(__FUNCTION__);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function endpoint(string $name)
    {
        if (!isset($this->endpoints[$name])) {
            $class = __NAMESPACE__.'\\Endpoints\\'.ucfirst($name);

            $this->endpoints[$name] = new $class($this->apiKey);
        }

        return $this->endpoints[$name];
    }
}
