<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use McMatters\ImportIo\Helpers\Validation;
use Throwable;

use function array_filter;
use function array_merge;
use function ceil;
use function count;
use function implode;
use function json_decode;
use function min;
use function uasort;

use const false;
use const null;
use const true;

/**
 * Class Store
 *
 * @package McMatters\ImportIo\Endpoints
 */
class Store extends Endpoint
{
    public const STATE_PENDING = 'PENDING';
    public const STATE_FINISHED = 'FINISHED';
    public const STATE_FAILED = 'FAILED';

    public const TYPE_REMOVED = 'REMOVED';
    public const TYPE_ADDED = 'ADDED';
    public const TYPE_SKIPPED = 'SKIPPED';
    public const TYPE_CHANGED = 'CHANGED';

    public const LIMIT_PAGE = 15;
    public const LIMIT_COUNT = 1000;

    /**
     * @var string
     */
    protected $subDomain = 'store';

    /**
     * @param string|null $extractorId
     * @param array $filters
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function searchCrawlRuns(
        string $extractorId = null,
        array $filters = []
    ): array {
        if (null !== $extractorId) {
            Validation::checkExtractorId($extractorId);
        }

        return $this->httpClient->get(
            'crawlrun/_search',
            array_filter(['extractorId' => $extractorId] + $filters),
        );
    }

    /**
     * @param string|null $extractorId
     * @param array $filters
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getFirstCrawlRun(
        string $extractorId = null,
        array $filters = []
    ): array {
        $crawlRuns = $this->searchCrawlRuns(
            $extractorId,
            ['_page' => 1, '_perpage' => 1] + $filters,
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
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getLastFinishedCrawlRun(string $extractorId = null): array
    {
        return $this->getFirstCrawlRun(
            $extractorId,
            [
                '_sort' => '_meta.creationTimestamp',
                'state' => self::STATE_FINISHED,
            ],
        );
    }

    /**
     * @param array $filters
     *
     * @return array
     *
     * @throws \Throwable
     */
    public function searchExtractors(array $filters = []): array
    {
        return $this->httpClient->get('store/extractor/_search', $filters);
    }

    /**
     * @return array
     */
    public function getAllExtractors(): array
    {
        return $this->getAllEntities('searchExtractors', [], [
            'q' => '_missing_:archived OR archived:false',
        ]);
    }

    /**
     * @param string $extractorId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getExtractorInfo(string $extractorId): array
    {
        Validation::checkExtractorId($extractorId);

        return $this->httpClient->get("store/extractor/{$extractorId}");
    }

    /**
     * @param string $crawlRunId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getCrawlRunProgress(string $crawlRunId): array
    {
        Validation::checkCrawlRunId($crawlRunId);

        return $this->httpClient->get("crawlrun/{$crawlRunId}");
    }

    /**
     * @param string $crawlRunId
     * @param string $attachmentId
     * @param string $type
     *
     * @return array|string
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function downloadFileForCrawlRun(
        string $crawlRunId,
        string $attachmentId,
        string $type = 'json'
    ) {
        Validation::checkCrawlRunId($crawlRunId);
        Validation::checkAttachmentId($attachmentId);
        Validation::checkDownloadableCrawlRunType($type);

        return $this->httpClient->get(
            "crawlRun/{$crawlRunId}/_attachment/{$type}/{$attachmentId}",
            [],
            $this->getAcceptDownloadType($type),
        );
    }

    /**
     * @param string $extractorId
     * @param array $urlList
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function uploadUrlListForExtractor(
        string $extractorId,
        array $urlList
    ): array {
        Validation::checkExtractorId($extractorId);

        $data = $this->httpClient->put(
            "extractor/{$extractorId}/_attachment/urlList",
            implode("\n", $urlList),
            'plain',
        );

        return json_decode($data, true);
    }

    /**
     * @param string $extractorId
     * @param string $attachmentId
     *
     * @return array|string
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function downloadUrlListFromExtractor(
        string $extractorId,
        string $attachmentId
    ) {
        Validation::checkExtractorId($extractorId);
        Validation::checkAttachmentId($attachmentId);

        return $this->httpClient->get(
            "extractor/{$extractorId}/_attachment/urlList/{$attachmentId}",
            [],
            'plain',
        );
    }

    /**
     * @param string $extractorId
     * @param array $filters
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getAllCrawlRuns(string $extractorId, array $filters = []): array
    {
        return $this->getAllEntities('searchCrawlRuns', [$extractorId], $filters);
    }

    /**
     * @param string $extractorId
     * @param bool $flatten
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getAllDataFromCrawlRuns(
        string $extractorId,
        bool $flatten = true
    ): array {
        $crawlRuns = $this->getAllCrawlRuns($extractorId);

        $data = [];

        foreach ($crawlRuns as $crawlRun) {
            $failedCount = $crawlRun['fields']['failedUrlCount'] ?? 0;
            $totalCount = $crawlRun['fields']['totalUrlCount'] ?? 0;
            $successCount = $crawlRun['fields']['successUrlCount'] ?? 0;

            if ($failedCount === $totalCount) {
                continue;
            }

            if (($crawlRun['fields']['rowCount'] ?? 0) === 0) {
                continue;
            }

            if ($totalCount !== ($successCount + $failedCount)) {
                continue;
            }

            $data[] = $this->downloadFileForCrawlRun(
                $crawlRun['_id'],
                $crawlRun['fields']['json'],
            );
        }

        return $flatten ? array_merge([], ...$data) : $data;
    }

    /**
     * @param string $reportId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getReport(string $reportId): array
    {
        Validation::checkReportId($reportId);

        return $this->httpClient->get("report/{$reportId}");
    }

    /**
     * @param string $extractorId
     *
     * @return array
     *
     * @throws \Throwable
     */
    public function getLastReportForExtractor(string $extractorId): array
    {
        $reports = [];

        foreach ($this->getAllReportRunsForExtractor($extractorId) as $reportRun) {
            if (!isset($reportRun['fields']['reportId'])) {
                continue;
            }

            $timestamp = $reportRun['fields']['_meta']['creationTimestamp'];

            if (!isset($reports[$reportRun['fields']['reportId']])) {
                $reports[$reportRun['fields']['reportId']] = [
                    'name' => $reportRun['fields']['name'],
                    'token' => $reportRun['fields']['reportId'],
                    'configId' => $reportRun['fields']['configId'],
                    'timestamp' => $timestamp,
                ];
            } elseif ($timestamp > $reports[$reportRun['fields']['reportId']]['timestamp']) {
                $reports[$reportRun['fields']['reportId']]['timestamp'] = $timestamp;
            }
        }

        uasort($reports, static function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        foreach ($reports as $report) {
            return $report;
        }

        return [];
    }

    /**
     * @param string $extractorId
     * @param string $name
     * @param array $primaryKey
     * @param array $columns
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function createReport(
        string $extractorId,
        string $name,
        array $primaryKey,
        array $columns = []
    ): array {
        Validation::checkExtractorId($extractorId);

        $report = $this->httpClient->post('store/report', [
            'type' => 'CRAWL_DIFF',
            'name' => $name,
        ]);

        $this->httpClient->post('store/reportconfiguration', [
            'extractorId' => $extractorId,
            'reportId' => $report['guid'],
            'config' => [
                'columns' => $columns,
                'primaryKey' => $primaryKey,
                'type' => 'CRAWL_DIFF',
            ],
        ]);

        return $report;
    }

    /**
     * @param string|null $reportId
     * @param array $filters
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function searchReportRuns(
        string $reportId = null,
        array $filters = []
    ): array {
        if (null !== $reportId) {
            Validation::checkReportId($reportId);
        }

        return $this->httpClient->get(
            'reportRun/_search',
            array_filter(['reportId' => $reportId] + $filters),
        );
    }

    /**
     * @param string|null $reportId
     * @param array $filters
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getFirstReportRun(
        string $reportId = null,
        array $filters = []
    ): array {
        $reportRuns = $this->searchReportRuns(
            $reportId,
            ['_page' => 1, '_perpage' => 1] + $filters,
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
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getLastFinishedReportRun(string $reportId = null): array
    {
        return $this->getFirstReportRun(
            $reportId,
            [
                '_sort' => '_meta.creationTimestamp',
                'status' => self::STATE_FINISHED,
            ],
        );
    }

    /**
     * @param int $timestamp
     * @param string|null $reportId
     *
     * @return array
     */
    public function getFinishedReportRunsAfter(
        int $timestamp,
        string $reportId = null
    ): array {
        $filters = ['q' => "timestamp:>{$timestamp}"];

        return $this->getAllReportRuns($filters, $reportId);
    }

    /**
     * @param array $filters
     * @param string|null $reportId
     *
     * @return array
     */
    public function getAllReportRuns(
        array $filters = [],
        string $reportId = null
    ): array {
        return $this->getAllEntities('searchReportRuns', [$reportId], $filters);
    }

    /**
     * @param string $extractorId
     * @param int $attempts
     *
     * @return array
     *
     * @throws \Throwable
     */
    public function getAllReportRunsForExtractor(
        string $extractorId,
        int $attempts = 0
    ): array {
        try {
            return $this->getAllReportRuns([
                'q' => "extractorId:{$extractorId}",
            ]);
        } catch (Throwable $e) {
            if ($attempts > 3) {
                throw $e;
            }

            return $this->getAllReportRunsForExtractor($extractorId, ++$attempts);
        }
    }

    /**
     * @param string $reportRunId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getReportRun(string $reportRunId): array
    {
        Validation::checkReportRunId($reportRunId);

        return $this->httpClient->get("reportRun/{$reportRunId}");
    }

    /**
     * @param string $reportRunId
     * @param string $attachmentId
     * @param string $type
     *
     * @return array|string
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function downloadFileForReportRun(
        string $reportRunId,
        string $attachmentId,
        string $type = 'json'
    ) {
        Validation::checkReportRunId($reportRunId);
        Validation::checkAttachmentId($attachmentId);
        Validation::checkDownloadableReportRunType($type);

        return $this->httpClient->get(
            "reportRun/{$reportRunId}/_attachment/{$type}/{$attachmentId}",
            [],
            $type === 'json' ? 'jsonl' : 'plain',
        );
    }

    /**
     * @param string $extractorId
     * @param string $url
     * @param array $headers
     *
     * @return array|string
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function addWebhookUrlToExtractor(
        string $extractorId,
        string $url,
        array $headers = []
    ) {
        Validation::checkExtractorId($extractorId);

        return $this->httpClient->patch(
            "store/extractor/{$extractorId}",
            [
                'webhooks' => [
                    [
                        'url' => $url,
                        'headers' => $headers,
                    ],
                ],
            ],
        );
    }

    /**
     * @param string $configId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function getRuntimeConfiguration(string $configId): array
    {
        Validation::checkUuid($configId, 'configId');

        return $this->httpClient->get("store/runtimeconfiguration/{$configId}");
    }

    /**
     * @param string $extractorId
     * @param array $body
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function updateExtractor(string $extractorId, array $body): array
    {
        Validation::checkExtractorId($extractorId);

        return $this->httpClient->patch(
            "store/extractor/{$extractorId}",
            $body,
        );
    }

    /**
     * @param string $method
     * @param array $args
     * @param array $filters
     * @param int|null $remaining
     * @param bool $oldest
     *
     * @return array
     */
    protected function getAllEntities(
        string $method,
        array $args = [],
        array $filters = [],
        int $remaining = null,
        bool $oldest = true
    ): array {
        $page = 1;
        $items = [];
        $processed = 0;
        $maxPages = 0;

        do {
            $arguments = array_merge(
                $args,
                [
                    $filters + [
                        '_page' => $page,
                        '_perpage' => min($remaining ?? self::LIMIT_COUNT, self::LIMIT_COUNT),
                        '_sort' => '_meta.creationTimestamp',
                        '_mine' => 'true',
                        '_sortDirection' => $oldest ? 'DESC' : 'ASC',
                    ],
                ],
            );

            $content = $this->$method(...$arguments);

            $countItems = count($content['hits']['hits']);

            if ($countItems > 0) {
                $items[] = $content['hits']['hits'];
            }

            $processed += $countItems;

            if ($page === 1 && $processed) {
                $maxPages = (int) ceil($content['hits']['total'] / $processed);
            }

            if (null !== $remaining) {
                $remaining -= $countItems;
            }

            $page++;
        } while (
            $content['hits']['total'] > 0 &&
            ($content['hits']['total'] > $processed && $page <= self::LIMIT_PAGE) &&
            ($remaining || null === $remaining)
        );

        if ($maxPages > self::LIMIT_PAGE && null === $remaining) {
            return array_merge(
                [],
                $this->getAllEntities(
                    $method,
                    $args,
                    $filters,
                    $content['hits']['total'] - $processed,
                    false,
                ),
                ...$items,
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
