<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;

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
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function extractorQuery(string $extractorId, string $url): array
    {
        $this->checkExtractorId($extractorId);

        return $this->requestGet(
            "extractor/{$extractorId}",
            ['query' => ['url' => $url]]
        );
    }
}
