<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Tests;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class StoreTest
 *
 * @package McMatters\ImportIo\Tests
 */
class StoreTest extends TestCase
{
    /**
     * Test "searchCrawlRuns" method.
     */
    public function testSearchCrawlRuns()
    {
        // Without query parameters.
        $data = $this->importIo->store->searchCrawlRuns();

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);

        // With extractorId parameter.
        $data = $this->importIo->store->searchCrawlRuns($this->extractorId);

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);

        // With page and perPage parameters.
        $data = $this->importIo->store->searchCrawlRuns(null, 1, 1);

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);

        // With everything except sortBy.
        $data = $this->importIo->store->searchCrawlRuns($this->extractorId, 1, 1);

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);
    }

    /**
     * Test "getCrawlRunProgress" method.
     */
    public function testGetCrawlRunProgress()
    {
        $crawlRun = $this->getFirstCrawlRun();

        $data = $this->importIo->store->getCrawlRunProgress($crawlRun['_id']);

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);
        $this->assertSame($crawlRun['_id'], $data['content']['guid']);
    }

    /**
     * Test "downloadFileFromCrawlRun" method with all types.
     */
    public function testDownloadFileFromCrawlRun()
    {
        $crawlRun = $this->getFirstCrawlRun();

        foreach (['json', 'csv', 'sample', 'log'] as $type) {
            $data = $this->importIo->store->downloadFileFromCrawlRun(
                $crawlRun['_id'],
                $crawlRun['fields'][$type],
                $type
            );

            $this->assertNotEmpty($data['content']);
            $this->assertNotEmpty($data['headers']);
            $this->assertSame(200, $data['code']);
        }
    }

    /**
     * Test "downloadFileFromCrawlRun" with exception.
     */
    public function testDownloadFileFromCrawlRunWithException()
    {
        $this->expectException(InvalidArgumentException::class);

        $crawlRun = $this->getFirstCrawlRun();

        $this->importIo->store->downloadFileFromCrawlRun(
            $crawlRun['_id'],
            $crawlRun['fields']['json'],
            'html'
        );
    }

    /**
     * Test "uploadUrlListForExtractor" method.
     */
    public function testUploadUrlListForExtractor()
    {
        $data = $this->importIo->store->uploadUrlListForExtractor(
            $this->extractorId,
            ['https://example.com', 'http://example.com']
        );

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);
    }

    public function testDownloadUrlListFromExtractor()
    {
        $crawlRun = $this->getFirstCrawlRun($this->extractorId);

        $data = $this->importIo->store->downloadUrlListFromExtractor(
            $this->extractorId,
            $crawlRun['fields']['urlListId']
        );

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);
    }

    /**
     * @param string|null $extractorId
     *
     * @return array
     * @throws RuntimeException
     */
    protected function getFirstCrawlRun(string $extractorId = null): array
    {
        static $crawlRun;

        if (null !== $crawlRun && null === $extractorId) {
            return $crawlRun;
        }

        $crawlRuns = $this->importIo->store->searchCrawlRuns($extractorId, 1, 1);

        if (!isset($crawlRuns['content']['hits']['hits'])) {
            throw new RuntimeException('There is no crawlRuns');
        }

        foreach ($crawlRuns['content']['hits']['hits'] as $hit) {
            if ($hit['_type'] === 'CrawlRun') {
                $crawlRun = $hit;

                return $hit;
            }
        }

        throw new RuntimeException('There is no crawlRuns');
    }
}
