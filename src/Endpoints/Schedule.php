<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use function array_merge, preg_match;

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
     * @throws ImportIoException
     */
    public function list(): array
    {
        return $this->requestGet('extractor');
    }

    /**
     * @param string $extractorId
     * @param string $interval
     * @param array $additional
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function create(
        string $extractorId,
        string $interval,
        array $additional = []
    ): array {
        $this->checkExtractorId($extractorId);
        $this->checkInterval($interval);

        $body = array_merge($additional, [
            'interval'    => $interval,
            'extractorId' => $extractorId,
        ]);

        return $this->requestPost('extractor', $body);
    }

    /**
     * @param string $extractorId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getByExtractorId(string $extractorId): array
    {
        $this->checkExtractorId($extractorId);

        return $this->requestGet("extractor/{$extractorId}");
    }

    /**
     * @param string $extractorId
     *
     * @return int
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function delete(string $extractorId): int
    {
        $this->checkExtractorId($extractorId);

        return $this->requestDelete("extractor/{$extractorId}");
    }

    /**
     * @param string $interval
     *
     * @throws InvalidArgumentException
     */
    protected function checkInterval(string $interval)
    {
        if (!preg_match('/^[\s\d\*\/,-]+$/', $interval)) {
            throw new InvalidArgumentException('Invalid interval was passed');
        }
    }
}
