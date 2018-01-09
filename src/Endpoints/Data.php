<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use McMatters\ImportIo\Helpers\Validation;

/**
 * Class Data
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Data extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'data';

    /**
     * @param string $extractorId
     * @param string $type
     *
     * @return array|string
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getLatestData(
        string $extractorId,
        string $type = 'json'
    ) {
        Validation::checkExtractorId($extractorId);
        Validation::checkDataType($type);

        return $this->requestGet(
            "extractor/{$extractorId}/{$type}/latest",
            [],
            $type === 'json' ? 'jsonl' : 'csv'
        );
    }
}
