<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use const null, true;
use function array_filter, implode, in_array, json_decode;

/**
 * Class Store
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Store extends Endpoint
{
    /**
     * @var string
     */
    protected $subDomain = 'store';

    /**
     * @param string|null $extractorId
     * @param int|null $page
     * @param int|null $perPage
     * @param string|null $sortBy
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function searchCrawlRuns(
        string $extractorId = null,
        int $page = null,
        int $perPage = null,
        string $sortBy = null
    ): array {
        if (null !== $extractorId) {
            $this->checkExtractorId($extractorId);
        }

        $query = array_filter([
            'extractorId' => $extractorId,
            '_page'       => $page,
            '_perpage'    => $perPage,
            '_sort'       => $sortBy,
        ]);

        return $this->requestGet('crawlrun/_search', ['query' => $query]);
    }

    /**
     * @param string $crawlRunId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getCrawlRunProgress(string $crawlRunId): array
    {
        $this->checkCrawlRunId($crawlRunId);

        return $this->requestGet("crawlrun/{$crawlRunId}");
    }

    /**
     * @param string $crawlRunId
     * @param string $attachmentId
     * @param string $type
     *
     * @return mixed
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function downloadFileFromCrawlRun(
        string $crawlRunId,
        string $attachmentId,
        string $type = 'json'
    ) {
        $this->checkCrawlRunId($crawlRunId);
        $this->checkAttachmentId($attachmentId);
        $this->checkDownloadType($type);

        return $this->requestGet(
            "crawlRun/{$crawlRunId}/_attachment/{$type}/{$attachmentId}",
            [],
            $this->getAcceptDownloadType($type)
        );
    }

    /**
     * @param string $extractorId
     * @param array $urlList
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function uploadUrlListForExtractor(
        string $extractorId,
        array $urlList
    ): array {
        $this->checkExtractorId($extractorId);

        $data = $this->requestPut(
            "extractor/{$extractorId}/_attachment/urlList",
            implode("\n", $urlList),
            [],
            'plain'
        );

        return json_decode($data, true);
    }

    /**
     * @param string $extractorId
     * @param string $attachmentId
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @throws ImportIoException
     */
    public function downloadUrlListFromExtractor(
        string $extractorId,
        string $attachmentId
    ) {
        $this->checkExtractorId($extractorId);
        $this->checkAttachmentId($attachmentId);

        return $this->requestGet(
            "extractor/{$extractorId}/_attachment/urlList/{$attachmentId}",
            [],
            'plain'
        );
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getAcceptDownloadType(string $type = 'json'): string
    {
        switch ($type) {
            case 'csv':
            case 'log':
                return 'csv';

            case 'sample':
                return 'json';

            case 'json':
            default:
                return 'jsonl';
        }
    }

    /**
     * @param string $type
     *
     * @throws InvalidArgumentException
     */
    protected function checkDownloadType(string $type = 'json')
    {
        if (!in_array($type, ['json', 'csv', 'log', 'sample'], true)) {
            throw new InvalidArgumentException('Incompatible type was passed');
        }
    }
}
