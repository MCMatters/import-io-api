<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;
use Throwable;

use const null;

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
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getRuns(string $extractorId): array
    {
        Validation::checkExtractorId($extractorId);

        return $this->httpClient->get(
            "extractor/{$extractorId}/runs",
            [],
            'xml',
        );
    }

    /**
     * @param string $extractorId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getRunsGuids(string $extractorId): array
    {
        $guids = [];

        $data = $this->getRuns($extractorId);

        try {
            $items = ((array) $data['channel'])['item'] ?? [];
        } catch (Throwable $e) {
            $items = [];
        }

        foreach ($items as $item) {
            if ($guid = ((array) $item)['guid'] ?? null) {
                $guids[] = $guid;
            }
        }

        return $guids;
    }
}
