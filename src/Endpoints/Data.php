<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;
use McMatters\Ticl\Enums\HttpStatusCode;
use Throwable;

use function array_change_key_case;

use const false, true, CASE_LOWER;

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
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
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
     *
     * @throws \Throwable
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
        $firstQueryHeaders = array_change_key_case($firstQuery->getHeaders(), CASE_LOWER);

        if ($firstQueryStatusCode >= HttpStatusCode::MOVED_PERMANENTLY &&
            $firstQueryStatusCode <= HttpStatusCode::PERMANENT_REDIRECT &&
            !empty($firstQueryHeaders['location'])
        ) {
            try {
                $this->httpClient->head(
                    $firstQueryHeaders['location'],
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
