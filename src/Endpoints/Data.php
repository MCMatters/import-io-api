<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\Ticl\Enums\HttpStatusCode;
use Throwable;

use function array_change_key_case;

use const false;
use const true;

class Data extends Endpoint
{
    protected string $subDomain = 'data';

    public function getLatestData(
        string $extractorId,
        string $type = 'json',
    ): array|string {
        return $this->httpClient->get(
            "extractor/{$extractorId}/{$type}/latest",
            [],
            'json' === $type ? 'jsonl' : 'csv',
        );
    }

    public function checkDataAccessibility(string $extractorId): bool
    {
        $firstQuery = $this->httpClient->request(
            'get',
            "extractor/{$extractorId}/json/latest",
            ['follow_redirects' => false],
        );

        $firstQueryStatusCode = $firstQuery->getStatusCode();
        $firstQueryHeaders = array_change_key_case($firstQuery->getHeaders());

        if (
            $firstQueryStatusCode >= HttpStatusCode::MOVED_PERMANENTLY &&
            $firstQueryStatusCode <= HttpStatusCode::PERMANENT_REDIRECT &&
            !empty($firstQueryHeaders['location'])
        ) {
            try {
                $this->httpClient->head(
                    $firstQueryHeaders['location'],
                    ['skip_base_uri' => true],
                );

                return true;
            } catch (Throwable) {
                return false;
            }
        }

        return false;
    }
}
