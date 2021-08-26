<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;

use function array_merge;

/**
 * Class Schedule
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Schedule extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'schedule';

    /**
     * @return array
     *
     * @throws \Throwable
     */
    public function list(): array
    {
        return $this->httpClient->get('extractor');
    }

    /**
     * @param string $extractorId
     * @param string $interval
     * @param array $additional
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function create(
        string $extractorId,
        string $interval,
        array $additional = []
    ): array {
        Validation::checkExtractorId($extractorId);
        Validation::checkScheduleInterval($interval);

        $body = array_merge($additional, [
            'interval' => $interval,
            'extractorId' => $extractorId,
        ]);

        return $this->httpClient->post('extractor', $body);
    }

    /**
     * @param string $extractorId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getByExtractorId(string $extractorId): array
    {
        Validation::checkExtractorId($extractorId);

        return $this->httpClient->get("extractor/{$extractorId}");
    }

    /**
     * @param string $extractorId
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function delete(string $extractorId): int
    {
        Validation::checkExtractorId($extractorId);

        return $this->httpClient->delete("extractor/{$extractorId}");
    }
}
