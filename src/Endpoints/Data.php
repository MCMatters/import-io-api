<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use const true;
use function in_array;

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
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getLatestData(
        string $extractorId,
        string $type = 'json'
    ): array {
        $this->checkExtractorId($extractorId);
        $this->checkType($type);

        $accept = $type === 'json' ? 'jsonl' : 'csv';

        return $this->requestGet(
            "extractor/{$extractorId}/{$type}/latest",
            [],
            $accept
        );
    }

    /**
     * @param string $type
     *
     * @throws InvalidArgumentException
     */
    protected function checkType(string $type)
    {
        if (!in_array($type, ['json', 'csv'], true)) {
            throw new InvalidArgumentException('Allowed types are only json and csv');
        }
    }
}
