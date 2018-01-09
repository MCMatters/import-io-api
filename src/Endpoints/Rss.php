<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use McMatters\ImportIo\Helpers\Validation;
use Throwable;

/**
 * Class Rss
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Rss extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'rss';

    /**
     * @param string $extractorId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getRuns(string $extractorId): array
    {
        Validation::checkExtractorId($extractorId);

        return $this->requestGet("extractor/{$extractorId}/runs", [], 'xml');
    }

    /**
     * @param string $extractorId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getRunsGuids(string $extractorId): array
    {
        $guids = [];

        $data = $this->getRuns($extractorId);

        try {
            $items = ((array) $data['channel'])['item'];
        } catch (Throwable $e) {
            $items = [];
        }

        foreach ($items as $item) {
            $guids[] = ((array) $item)['guid'];
        }

        return $guids;
    }
}
