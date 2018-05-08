<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use McMatters\ImportIo\Helpers\Validation;
use const false, null, true;
use function array_filter, array_merge, ceil, count, implode, json_decode, min;

/**
 * Class Store
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Store extends Endpoint
{
    const STATE_PENDING = 'PENDING';
    const STATE_FINISHED = 'FINISHED';
    const STATE_FAILED = 'FAILED';

    const TYPE_REMOVED = 'REMOVED';
    const TYPE_ADDED = 'ADDED';
    const TYPE_SKIPPED = 'SKIPPED';
    const TYPE_CHANGED = 'CHANGED';

    const LIMIT_PAGE = 15;
    const LIMIT_COUNT = 1000;

    /**
     * @var string
     */
    protected $subDomain = 'store';

    /**
     * @param string|null $extractorId
     * @param array $filters
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function searchCrawlRuns(
        string $extractorId = null,
        array $filters = []
    ): array {
        if (null !== $extractorId) {
            Validation::checkExtractorId($extractorId);
        }

        $query = array_filter(['extractorId' => $extractorId] + $filters);

        return $this->requestGet('crawlrun/_search', ['query' => $query]);
    }

    /**
     * @param string|null $extractorId
     * @param array $filters
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getFirstCrawlRun(
        string $extractorId = null,
        array $filters = []
    ): array {
        $crawlRuns = $this->searchCrawlRuns(
            $extractorId,
            ['_page' => 1, '_perpage' => 1] + $filters
        );

        if (empty($crawlRuns['hits']['hits'])) {
            return [];
        }

        foreach ($crawlRuns['hits']['hits'] as $hit) {
            if ($hit['_type'] === 'CrawlRun') {
                return $hit;
            }
        }

        return [];
    }

    /**
     * @param string|null $extractorId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getLastFinishedCrawlRun(string $extractorId = null): array
    {
        return $this->getFirstCrawlRun(
            $extractorId,
            [
                '_sort' => '_meta.creationTimestamp',
                'state' => self::STATE_FINISHED,
            ]
        );
    }

    /**
     * @param array $filters
     *
     * @return array
     * @throws ImportIoException
     */
    public function searchExtractors(array $filters = []): array
    {
        return $this->requestGet('store/extractor/_search', ['query' => $filters]);
    }

    /**
     * @return array
     */
    public function getAllExtractors(): array
    {
        return $this->getAllEntities('searchExtractors');
    }

    /**
     * @param string $extractorId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getExtractorInfo(string $extractorId): array
    {
        Validation::checkExtractorId($extractorId);

        return $this->requestGet("store/extractor/{$extractorId}");
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
        Validation::checkCrawlRunId($crawlRunId);

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
    public function downloadFileForCrawlRun(
        string $crawlRunId,
        string $attachmentId,
        string $type = 'json'
    ) {
        Validation::checkCrawlRunId($crawlRunId);
        Validation::checkAttachmentId($attachmentId);
        Validation::checkDownloadableCrawlRunType($type);

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
        Validation::checkExtractorId($extractorId);

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
        Validation::checkExtractorId($extractorId);
        Validation::checkAttachmentId($attachmentId);

        return $this->requestGet(
            "extractor/{$extractorId}/_attachment/urlList/{$attachmentId}",
            [],
            'plain'
        );
    }

    /**
     * @param string $extractorId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getAllCrawlRuns(string $extractorId): array
    {
        return $this->getAllEntities('searchCrawlRuns', [$extractorId]);
    }

    /**
     * @param string $extractorId
     * @param bool $flatten
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getAllDataFromCrawlRuns(
        string $extractorId,
        bool $flatten = true
    ): array {
        $crawlRuns = $this->getAllCrawlRuns($extractorId);

        $data = [];

        foreach ($crawlRuns as $crawlRun) {
            if ($crawlRun['fields']['state'] !== self::STATE_FINISHED) {
                continue;
            }

            $data[] = $this->downloadFileForCrawlRun(
                $crawlRun['_id'],
                $crawlRun['fields']['json']
            );
        }

        return $flatten ? array_merge([], ...$data) : $data;
    }

    /**
     * @param string $reportId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getReport(string $reportId): array
    {
        Validation::checkReportId($reportId);

        return $this->requestGet("report/{$reportId}");
    }

    /**
     * @param string|null $reportId
     * @param array $filters
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function searchReportRuns(
        string $reportId = null,
        array $filters = []
    ): array {
        if (null !== $reportId) {
            Validation::checkReportId($reportId);
        }

        $query = array_filter(['reportId' => $reportId] + $filters);

        return $this->requestGet('reportRun/_search', ['query' => $query]);
    }

    /**
     * @param string|null $reportId
     * @param array $filters
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getFirstReportRun(
        string $reportId = null,
        array $filters = []
    ): array {
        $reportRuns = $this->searchReportRuns(
            $reportId,
            ['_page' => 1, '_perpage' => 1] + $filters
        );

        if (empty($reportRuns['hits']['hits'])) {
            return [];
        }

        foreach ($reportRuns['hits']['hits'] as $hit) {
            if ($hit['_type'] === 'ReportRun') {
                return $hit;
            }
        }

        return [];
    }

    /**
     * @param string|null $reportId
     *
     * @return array
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function getLastFinishedReportRun(string $reportId = null): array
    {
        return $this->getFirstReportRun(
            $reportId,
            [
                '_sort'  => '_meta.creationTimestamp',
                'status' => self::STATE_FINISHED,
            ]
        );
    }

    /**
     * @return array
     */
    public function getAllReportRuns(): array
    {
        return $this->getAllEntities('searchReportRuns', [null]);
    }

    /**
     * @param string $reportRunId
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws ImportIoException
     */
    public function getReportRun(string $reportRunId): array
    {
        Validation::checkReportRunId($reportRunId);

        return $this->requestGet("reportRun/{$reportRunId}");
    }

    /**
     * @param string $reportRunId
     * @param string $attachmentId
     * @param string $type
     *
     * @return mixed
     * @throws ImportIoException
     * @throws InvalidArgumentException
     */
    public function downloadFileForReportRun(
        string $reportRunId,
        string $attachmentId,
        string $type = 'json'
    ) {
        Validation::checkReportRunId($reportRunId);
        Validation::checkAttachmentId($attachmentId);
        Validation::checkDownloadableReportRunType($type);

        return $this->requestGet(
            "reportRun/{$reportRunId}/_attachment/{$type}/{$attachmentId}",
            [],
            $type === 'json' ? 'jsonl' : 'plain'
        );
    }

    /**
     * @param string $method
     * @param array $args
     * @param int|null $remaining
     * @param bool $oldest
     *
     * @return array
     */
    protected function getAllEntities(
        string $method,
        array $args = [],
        int $remaining = null,
        bool $oldest = true
    ): array {
        $page = 1;
        $items = [];
        $processed = 0;
        $maxPages = 0;

        do {
            $content = $this->$method(...array_merge(
                $args,
                [[
                    '_page'          => $page,
                    '_perpage'       => min($remaining ?? self::LIMIT_COUNT, self::LIMIT_COUNT),
                    '_sort'          => '_meta.creationTimestamp',
                    '_mine'          => 'true',
                    '_sortDirection' => $oldest ? 'DESC' : 'ASC',
                ]]
            ));

            $countItems = count($content['hits']['hits']);

            $items[] = $content['hits']['hits'];
            $processed += $countItems;

            if ($page === 1) {
                $maxPages = (int) ceil($content['hits']['total'] / $processed);
            }

            if (null !== $remaining) {
                $remaining -= $countItems;
            }

            $page++;
        } while (
            ($content['hits']['total'] > $processed && $page <= self::LIMIT_PAGE) &&
            ((null !== $remaining && $remaining) || null === $remaining)
        );

        if ($maxPages > self::LIMIT_PAGE && null === $remaining) {
            return array_merge(
                [],
                $this->getAllEntities(
                    $method,
                    $args,
                    $content['hits']['total'] - $processed,
                    false
                ),
                ...$items
            );
        }

        return array_merge([], ...$items);
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

            case 'files':
                return 'zip';

            case 'pdf':
                return 'pdf';

            case 'xlsx':
                return 'xslx';

            case 'json':
            default:
                return 'jsonl';
        }
    }
}
