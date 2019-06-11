<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use McMatters\ImportIo\Helpers\Validation;
use McMatters\Ticl\Enums\HttpStatusCode;
use Throwable;
use const false, true;

/**
 * Class Data
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Data extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'data';

    /**
     * @param string $extractorId
     * @param string $type
     *
     * @return array|string
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getLatestData(
        string $extractorId,
        string $type = 'json'
    ) {
        Validation::checkExtractorId($extractorId);
        Validation::checkDataType($type);

        return $this->httpClient->get(
            "extractor/{$extractorId}/{$type}/latest",
            [],
            $type === 'json' ? 'jsonl' : 'csv'
        );
    }

    /**
     * @param string $extractorId
     *
     * @return bool
     */
    public function checkDataAccessibility(string $extractorId): bool
    {
        Validation::checkExtractorId($extractorId);

        $firstQuery = $this->httpClient->request(
            'get',
            "extractor/{$extractorId}/json/latest",
            ['follow_redirects' => false]
        );

        $firstQueryStatusCode = $firstQuery->getStatusCode();
        $firstQueryHeaders = $firstQuery->getHeaders();

        if ($firstQueryStatusCode >= HttpStatusCode::MOVED_PERMANENTLY &&
            $firstQueryStatusCode <= HttpStatusCode::PERMANENT_REDIRECT &&
            !empty($firstQueryHeaders['Location'])
        ) {
            try {
                $this->httpClient->head(
                    $firstQueryHeaders['Location'],
                    ['skip_base_uri' => true]
                );

                return true;
            } catch (Throwable $e) {
                return false;
            }
        }

        return false;
    }
}
