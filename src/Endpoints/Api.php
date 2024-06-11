<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

class Api extends Endpoint
{
    protected string $subDomain = 'api';

    public function launchReportRun(string $reportId): array|string
    {
        return $this->httpClient->post("report/{$reportId}/run");
    }

    public function getAllExtractors(): array
    {
        return $this->httpClient->get('maestro/extractor');
    }

    public function getCurrentUser(): array
    {
        return $this->httpClient->get('auth/currentuser');
    }
}
