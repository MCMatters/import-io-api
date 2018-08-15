<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
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
     * @return mixed
     * @throws InvalidArgumentException
     * @throws ImportIoException
     */
    public function launchReportRun(string $reportId)
    {
        Validation::checkReportId($reportId);

        return $this->httpClient->post("report/{$reportId}/run");
    }
}
