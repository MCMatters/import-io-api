<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;

/**
 * Class Extraction
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Extraction extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'extraction';

    /**
     * @param string $extractorId
     * @param string $url
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function extractorQuery(string $extractorId, string $url): array
    {
        Validation::checkExtractorId($extractorId);

        return $this->httpClient->get(
            "extractor/{$extractorId}",
            ['url' => $url]
        );
    }
}
