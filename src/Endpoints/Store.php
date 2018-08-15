<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use DateTime;
use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use McMatters\ImportIo\Helpers\Validation;
use Throwable;
use const false, null, true;
use function array_filter, array_merge, ceil, count, implode, json_decode, min, uasort;

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

        return $this->httpClient->get(
            'crawlrun/_search',
            array_filter(['extractorId' => $extractorId] + $filters)
        );
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
     * @throws ImportIoException
     * @throws InvalidArgumentException
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
     * @throws ImportIoException
     * @throws InvalidArgumentException
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

        return $this->httpClient->get(
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

        $data = $this->httpClient->put(
            "extractor/{$extractorId}/_attachment/urlList",
            implode("\n", $urlList),
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

        return $this->httpClient->get(
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

        return $this->httpClient->get("report/{$reportId}");
    }

    /**
     * @param string $extractorId
     *
     * @return array
     * @throws Throwable
     */
    public function getLastReportForExtractor(string $extractorId): array
    {
        $reports = [];

        foreach ($this->getAllReportRunsForExtractor($extractorId) as $reportRun) {
            if (!isset($reportRun['fields']['reportId'])) {
                continue;
            }

            if (!isset($reports[$reportRun['fields']['reportId']])) {
                $reports[$reportRun['fields']['reportId']] = [
                    'name' => $reportRun['fields']['name'],
                    'token' => $reportRun['fields']['reportId'],
                    'time' => (new DateTime())->setTimestamp(
                        (int) ($reportRun['fields']['_meta']['creationTimestamp'] / 1000)
                    ),
                ];
            } else {
                $newDate = (new DateTime())->setTimestamp(
                    (int) ($reportRun['fields']['_meta']['creationTimestamp'] / 1000)
                );

                if ($newDate > $reports[$reportRun['fields']['reportId']]['time']) {
                    $reports[$reportRun['fields']['reportId']]['time'] = $newDate;
                }
            }
        }

        uasort($reports, function ($a, $b) {
            return $a['time'] <=> $b['time'];
        });

        foreach ($reports as $report) {
            unset($report['time']);

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
     * @throws ImportIoException
     * @throws InvalidArgumentException
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

        return $this->httpClient->get(
            'reportRun/_search',
            array_filter(['reportId' => $reportId] + $filters)
        );
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
                '_sort' => '_meta.creationTimestamp',
                'status' => self::STATE_FINISHED,
            ]
        );
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    public function getAllReportRuns(array $filters = []): array
    {
        return $this->getAllEntities('searchReportRuns', [null, $filters]);
    }

    /**
     * @param string $extractorId
     * @param int $attempts
     *
     * @return array
     * @throws Throwable
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
     * @throws InvalidArgumentException
     * @throws ImportIoException
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

        return $this->httpClient->get(
            "reportRun/{$reportRunId}/_attachment/{$type}/{$attachmentId}",
            [],
            $type === 'json' ? 'jsonl' : 'plain'
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
                [$filters + [
                        '_page' => $page,
                        '_perpage' => min($remaining ?? self::LIMIT_COUNT, self::LIMIT_COUNT),
                        '_sort' => '_meta.creationTimestamp',
                        '_mine' => 'true',
                        '_sortDirection' => $oldest ? 'DESC' : 'ASC',
                    ]]
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
            ((null !== $remaining && $remaining) || null === $remaining)
        );

        if ($maxPages > self::LIMIT_PAGE && null === $remaining) {
            return array_merge(
                [],
                $this->getAllEntities(
                    $method,
                    $args,
                    $filters,
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
