<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;

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
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function startCrawl(string $extractorId): string
    {
        Validation::checkExtractorId($extractorId);

        $data = $this->httpClient->post("extractor/{$extractorId}/start");

        return $data['crawlRunId'] ?? '';
    }

    /**
     * @param string $extractorId
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function cancelCrawl(string $extractorId): string
    {
        Validation::checkExtractorId($extractorId);

        $data = $this->httpClient->post("extractor/{$extractorId}/cancel");

        return $data['crawlRunId'] ?? '';
    }
}
