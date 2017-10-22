<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;

/**
 * Class Run
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Run extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'run';

    /**
     * @param string $extractorId
     *
     * @return string
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function startCrawl(string $extractorId): string
    {
        $this->checkExtractorId($extractorId);

        $data = $this->requestPost("extractor/{$extractorId}/start");

        return $data['crawlRunId'] ?? '';
    }

    /**
     * @param string $extractorId
     *
     * @return string
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function cancelCrawl(string $extractorId): string
    {
        $this->checkExtractorId($extractorId);

        $data = $this->requestPost("extractor/{$extractorId}/cancel");

        return $data['crawlRunId'] ?? '';
    }
}
