<?php

declare(strict_types = 1);

namespace McMatters\ImportIo;

use function strtolower, ucfirst;

/**
 * Class ImportIo
 *
 * @package McMatters\ImportIo
 */
class ImportIo
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
     * @param string $name
     *
     * @return mixed
     */
    public function endpoint(string $name)
    {
        $name = strtolower($name);

        if (!isset($this->endpoints[$name])) {
            $class = __NAMESPACE__.'\\Endpoints\\'.ucfirst($name);

            $this->endpoints[$name] = new $class($this->apiKey);
        }

        return $this->endpoints[$name];
    }
}
