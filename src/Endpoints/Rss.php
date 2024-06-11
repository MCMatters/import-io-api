<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use Throwable;

use const null;

class Rss extends Endpoint
{
    protected string $subDomain = 'rss';

    public function getRuns(string $extractorId): array
    {
        return $this->httpClient->get(
            "extractor/{$extractorId}/runs",
            [],
            'xml',
        );
    }

    public function getRunsGuids(string $extractorId): array
    {
        $guids = [];

        $data = $this->getRuns($extractorId);

        try {
            $data = (array) $data['channel'];

            $items = $data['item'] ?? [];
        } catch (Throwable $e) {
            $items = [];
        }

        foreach ($items as $item) {
            $item = (array) $item;

            if ($guid = ($item['guid'] ?? null)) {
                $guids[] = $guid;
            }
        }

        return $guids;
    }
}
