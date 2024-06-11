<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use function array_merge;

class Schedule extends Endpoint
{
    protected string $subDomain = 'schedule';

    public function list(): array
    {
        return $this->httpClient->get('extractor');
    }

    public function create(
        string $extractorId,
        string $interval,
        array $additional = [],
    ): array {
        $body = array_merge($additional, [
            'interval' => $interval,
            'extractorId' => $extractorId,
        ]);

        return $this->httpClient->post('extractor', $body);
    }

    public function getByExtractorId(string $extractorId): array
    {
        return $this->httpClient->get("extractor/{$extractorId}");
    }

    public function delete(string $extractorId): int
    {
        return $this->httpClient->delete("extractor/{$extractorId}");
    }
}
