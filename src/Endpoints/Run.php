<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

class Run extends Endpoint
{
    protected string $subDomain = 'run';

    public function startCrawl(string $extractorId): string
    {
        $data = $this->httpClient->post("extractor/{$extractorId}/start");

        return $data['crawlRunId'] ?? '';
    }

    public function cancelCrawl(string $extractorId): string
    {
        $data = $this->httpClient->post("extractor/{$extractorId}/cancel");

        return $data['crawlRunId'] ?? '';
    }
}
