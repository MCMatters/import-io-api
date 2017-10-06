<?php

declare(strict_types = 1);

namespace McMatters\ImportIo;

use McMatters\ImportIo\Endpoints\{
    Data, Extraction, Rss, Run, Schedule, Store
};

/**
 * Class ImportIo
 *
 * @package McMatters\ImportIo
 */
class ImportIo
{
    /**
     * @var Data
     */
    public $data;

    /**
     * @var Extraction
     */
    public $extraction;

    /**
     * @var Rss
     */
    public $rss;

    /**
     * @var Run
     */
    public $run;

    /**
     * @var Schedule
     */
    public $schedule;

    /**
     * @var Store
     */
    public $store;

    /**
     * ImportIo constructor.
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->data = new Data($apiKey);
        $this->extraction = new Extraction($apiKey);
        $this->rss = new Rss($apiKey);
        $this->run = new Run($apiKey);
        $this->schedule = new Schedule($apiKey);
        $this->store = new Store($apiKey);
    }
}
