<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Endpoints;

use Throwable;

use function array_filter;
use function array_merge;
use function count;
use function implode;
use function json_decode;
use function uasort;

use const JSON_THROW_ON_ERROR;
use const null;
use const true;

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

    protected string $subDomain = 'store';

    public function searchCrawlRuns(
        ?string $extractorId = null,
        array $filters = [],
    ): array {
        return $this->httpClient->get(
            'crawlrun/_query',
            array_filter(['f_extractorId' => $extractorId] + $filters),
        );
    }

    public function getFirstCrawlRun(
        ?string $extractorId = null,
        array $filters = [],
    ): array {
        $crawlRuns = $this->searchCrawlRuns(
            $extractorId,
            ['_page' => 1, '_perpage' => 1] + $filters,
        );

        return $crawlRuns[0] ?? [];
    }

    public function getLastFinishedCrawlRun(?string $extractorId = null): array
    {
        return $this->getFirstCrawlRun(
            $extractorId,
            [
                '_sort' => 'meta_created_at',
                '_sortDirection' => 'DESC',
                'state' => self::STATE_FINISHED,
            ],
        );
    }

    public function searchExtractors(array $filters = []): array
    {
        return $this->httpClient->get('store/extractor/_query', $filters);
    }

    public function getAllExtractors(array $args = []): array
    {
        return $this->getAllEntities('searchExtractors', $args, [
            'q' => '_missing_:archived OR archived:false',
        ]);
    }

    public function getExtractorInfo(string $extractorId): array
    {
        return $this->httpClient->get("store/extractor/{$extractorId}");
    }

    public function getCrawlRunProgress(string $crawlRunId): array
    {
        return $this->httpClient->get("crawlrun/{$crawlRunId}");
    }

    public function downloadFileForCrawlRun(
        string $crawlRunId,
        string $attachmentId,
        string $type = 'json',
    ): array|string {
        return $this->httpClient->get(
            "crawlRun/{$crawlRunId}/_attachment/{$type}/{$attachmentId}",
            [],
            $this->getAcceptDownloadType($type),
        );
    }

    public function uploadUrlListForExtractor(
        string $extractorId,
        array $urlList,
    ): array {
        $data = $this->httpClient->put(
            "extractor/{$extractorId}/_attachment/urlList",
            implode("\n", $urlList),
            'plain',
        );

        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    public function downloadUrlListFromExtractor(
        string $extractorId,
        string $attachmentId,
    ): array|string {
        return $this->httpClient->get(
            "extractor/{$extractorId}/_attachment/urlList/{$attachmentId}",
            [],
            'plain',
        );
    }

    public function getAllCrawlRuns(string $extractorId, array $filters = []): array
    {
        return $this->getAllEntities('searchCrawlRuns', [$extractorId], $filters);
    }

    public function getAllDataFromCrawlRuns(
        string $extractorId,
        bool $flatten = true,
    ): array {
        $crawlRuns = $this->getAllCrawlRuns($extractorId);

        $data = [];

        foreach ($crawlRuns as $crawlRun) {
            $failedCount = $crawlRun['failedUrlCount'] ?? 0;
            $totalCount = $crawlRun['totalUrlCount'] ?? 0;
            $successCount = $crawlRun['successUrlCount'] ?? 0;

            if ($failedCount === $totalCount) {
                continue;
            }

            if (($crawlRun['rowCount'] ?? 0) === 0) {
                continue;
            }

            if ($totalCount !== ($successCount + $failedCount)) {
                continue;
            }

            $data[] = $this->downloadFileForCrawlRun(
                $crawlRun['guid'],
                $crawlRun['json'],
            );
        }

        return $flatten ? array_merge([], ...$data) : $data;
    }

    public function getReport(string $reportId): array
    {
        return $this->httpClient->get("report/{$reportId}");
    }

    public function getLastReportForExtractor(string $extractorId): array
    {
        $reports = [];

        foreach ($this->getAllReportRunsForExtractor($extractorId) as $reportRun) {
            if (!isset($reportRun['reportId'])) {
                continue;
            }

            $timestamp = $reportRun['_meta']['creationTimestamp'];

            if (!isset($reports[$reportRun['reportId']])) {
                $reports[$reportRun['reportId']] = [
                    'name' => $reportRun['name'],
                    'token' => $reportRun['reportId'],
                    'configId' => $reportRun['configId'],
                    'timestamp' => $timestamp,
                ];
            } elseif ($timestamp > $reports[$reportRun['reportId']]['timestamp']) {
                $reports[$reportRun['reportId']]['timestamp'] = $timestamp;
            }
        }

        uasort($reports, static fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        foreach ($reports as $report) {
            return $report;
        }

        return [];
    }

    public function createReport(
        string $extractorId,
        string $name,
        array $primaryKey,
        array $columns = [],
    ): array {
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

    public function searchReportRuns(
        ?string $reportId = null,
        array $filters = [],
    ): array {
        return $this->httpClient->get(
            'reportRun/_query',
            array_filter(['f_reportId' => $reportId] + $filters),
        );
    }

    public function getFirstReportRun(
        ?string $reportId = null,
        array $filters = []
    ): array {
        $reportRuns = $this->searchReportRuns(
            $reportId,
            ['_page' => 1, '_perpage' => 1] + $filters,
        );

        return $reportRuns[0] ?? [];
    }

    public function getLastFinishedReportRun(?string $reportId = null): array
    {
        return $this->getFirstReportRun(
            $reportId,
            [
                '_sort' => 'meta_created_at',
                '_sortDirection' => 'DESC',
                'status' => self::STATE_FINISHED,
            ],
        );
    }

    public function getFinishedReportRunsAfter(
        int $timestamp,
        ?string $reportId = null,
    ): array {
        $filters = ['q' => "timestamp:>{$timestamp}"];

        return $this->getAllReportRuns($filters, $reportId);
    }

    public function getAllReportRuns(
        array $filters = [],
        ?string $reportId = null,
    ): array {
        return $this->getAllEntities('searchReportRuns', [$reportId], $filters);
    }

    public function getAllReportRunsForExtractor(
        string $extractorId,
        int $attempts = 0,
    ): array {
        try {
            return $this->getAllReportRuns([
                'f_extractorId' => $extractorId,
            ]);
        } catch (Throwable $e) {
            if ($attempts > 3) {
                throw $e;
            }

            return $this->getAllReportRunsForExtractor($extractorId, ++$attempts);
        }
    }

    public function getReportRun(string $reportRunId): array
    {
        return $this->httpClient->get("reportRun/{$reportRunId}");
    }

    public function downloadFileForReportRun(
        string $reportRunId,
        string $attachmentId,
        string $type = 'json',
    ): array|string {
        return $this->httpClient->get(
            "reportRun/{$reportRunId}/_attachment/{$type}/{$attachmentId}",
            [],
            'json' === $type ? 'jsonl' : 'plain',
        );
    }

    public function addWebhookUrlToExtractor(
        string $extractorId,
        string $url,
        array $headers = [],
    ): array|string {
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

    public function getRuntimeConfiguration(string $configId): array
    {
        return $this->httpClient->get("store/runtimeconfiguration/{$configId}");
    }

    public function updateExtractor(string $extractorId, array $body): array
    {
        return $this->httpClient->patch(
            "store/extractor/{$extractorId}",
            $body,
        );
    }

    protected function getAllEntities(
        string $method,
        array $args = [],
        array $filters = [],
        bool $oldest = true,
    ): array {
        $page = 1;
        $items = [];

        $lastGuid = null;
        $guid = null;

        do {
            $arguments = array_merge(
                $args,
                [
                    $filters + [
                        '_page' => $page,
                        '_perpage' => self::LIMIT_COUNT,
                        '_mine' => 'true',
                        '_sort' => 'meta_created_at',
                        '_sortDirection' => $oldest ? 'DESC' : 'ASC',
                    ],
                ],
            );

            $content = $this->$method(...$arguments);

            $countItems = count($content);

            if ($countItems > 0) {
                $guid = $content[$countItems - 1]['guid'] ?? null;
            }

            if ($guid !== $lastGuid) {
                $items[] = $content;

                $lastGuid = $guid;
            } else {
                break;
            }

            $page++;
        } while ($countItems === self::LIMIT_COUNT);

        return array_merge([], ...$items);
    }

    protected function getAcceptDownloadType(string $type = 'json'): string
    {
        return match ($type) {
            'csv', 'log' => 'csv',
            'sample' => 'json',
            'files' => 'zip',
            'pdf' => 'pdf',
            'xlsx' => 'xslx',
            default => 'jsonl',
        };
    }
}
