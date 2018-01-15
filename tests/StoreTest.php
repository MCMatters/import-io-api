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
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testSearchCrawlRuns()
    {
        // Without query parameters.
        $data = $this->client->endpoint('store')->searchCrawlRuns();

        $this->assertNotEmpty($data);

        // With extractorId parameter.
        $data = $this->client->endpoint('store')->searchCrawlRuns($this->extractorId);

        $this->assertNotEmpty($data);

        // With page and perPage parameters.
        $data = $this->client->endpoint('store')->searchCrawlRuns(
            null,
            ['_page' => 1, '_perPage' => 1]
        );

        $this->assertNotEmpty($data);

        // With everything except sortBy.
        $data = $this->client->endpoint('store')->searchCrawlRuns(
            $this->extractorId,
            ['_page' => 1, '_perPage' => 1]
        );

        $this->assertNotEmpty($data);
    }

    /**
     * Test "getCrawlRunProgress" method.
     *
     * @throws RuntimeException
     */
    public function testGetCrawlRunProgress()
    {
        $crawlRun = $this->getFirstCrawlRun();

        $data = $this->client->endpoint('store')->getCrawlRunProgress($crawlRun['_id']);

        $this->assertNotEmpty($data);
        $this->assertSame($crawlRun['_id'], $data['guid']);
    }

    /**
     * Test "downloadFileForCrawlRun" method with all types.
     *
     * @throws RuntimeException
     */
    public function testDownloadFileForCrawlRun()
    {
        $crawlRun = $this->getFirstCrawlRun();

        foreach (['json', 'csv', 'sample', 'log'] as $type) {
            $data = $this->client->endpoint('store')->downloadFileForCrawlRun(
                $crawlRun['_id'],
                $crawlRun['fields'][$type],
                $type
            );

            $this->assertNotEmpty($data);
        }
    }

    /**
     * Test "downloadFileFromCrawlRun" with exception.
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws RuntimeException
     */
    public function testDownloadFileFromCrawlRunWithException()
    {
        $this->expectException(InvalidArgumentException::class);

        $crawlRun = $this->getFirstCrawlRun();

        $this->client->endpoint('store')->downloadFileForCrawlRun(
            $crawlRun['_id'],
            $crawlRun['fields']['json'],
            'html'
        );
    }

    /**
     * Test "uploadUrlListForExtractor" method.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testUploadUrlListForExtractor()
    {
        $data = $this->client->endpoint('store')->uploadUrlListForExtractor(
            $this->extractorId,
            ['https://example.com', 'http://example.com']
        );

        $this->assertNotEmpty($data);
    }

    /**
     * Test "downloadUrlListFromExtractor" method.
     *
     * @throws RuntimeException
     */
    public function testDownloadUrlListFromExtractor()
    {
        $crawlRun = $this->getFirstCrawlRun($this->extractorId);

        $data = $this->client->endpoint('store')->downloadUrlListFromExtractor(
            $this->extractorId,
            $crawlRun['fields']['urlListId']
        );

        $this->assertNotEmpty($data);
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

        $crawlRuns = $this->client->endpoint('store')->searchCrawlRuns(
            $extractorId,
            ['_page' => 1, '_perPage' => 1]
        );

        if (!isset($crawlRuns['hits']['hits'])) {
            throw new RuntimeException('There is no crawlRuns');
        }

        foreach ($crawlRuns['hits']['hits'] as $hit) {
            if ($hit['_type'] === 'CrawlRun') {
                $crawlRun = $hit;

                return $hit;
            }
        }

        throw new RuntimeException('There is no crawlRuns');
    }
}
