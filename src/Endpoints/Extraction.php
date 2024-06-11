<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

class Extraction extends Endpoint
{
    protected string $subDomain = 'extraction';

    public function extractorQuery(string $extractorId, string $url): array
    {
        return $this->httpClient->get(
            "extractor/{$extractorId}",
            ['url' => $url],
        );
    }
}
