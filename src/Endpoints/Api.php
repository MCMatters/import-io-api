<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;

/**
 * Class Api
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Api extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'api';

    /**
     * @param string $reportId
     *
     * @return array|string
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function launchReportRun(string $reportId)
    {
        Validation::checkReportId($reportId);

        return $this->httpClient->post("report/{$reportId}/run");
    }

    /**
     * @return array
     *
     * @throws \Throwable
     */
    public function getAllExtractors(): array
    {
        return $this->httpClient->get('maestro/extractor');
    }

    /**
     * @return array
     *
     * @throws \Throwable
     */
    public function getCurrentUser(): array
    {
        return $this->httpClient->get('auth/currentuser');
    }
}
