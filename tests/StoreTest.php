<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

use InvalidArgumentException;
use RuntimeException;

use const null;

class StoreTest extends TestCase
{
    public function testSearchCrawlRuns(): void
    {
        // Without query parameters.
        $data = $this->client->store()->searchCrawlRuns();

        $this->assertNotEmpty($data);

        // With extractorId parameter.
        $data = $this->client->store()->searchCrawlRuns($this->extractorId);

        $this->assertNotEmpty($data);

        // With page and perPage parameters.
        $data = $this->client->store()->searchCrawlRuns(
            null,
            ['_page' => 1, '_perpage' => 1],
        );

        $this->assertNotEmpty($data);

        // With everything except sortBy.
        $data = $this->client->store()->searchCrawlRuns(
            $this->extractorId,
            ['_page' => 1, '_perpage' => 1],
        );

        $this->assertNotEmpty($data);
    }

    public function testGetCrawlRunProgress(): void
    {
        $crawlRun = $this->getFirstCrawlRun();

        $data = $this->client->store()->getCrawlRunProgress($crawlRun['_id']);

        $this->assertNotEmpty($data);
        $this->assertSame($crawlRun['_id'], $data['guid']);
    }

    public function testDownloadFileForCrawlRun(): void
    {
        $crawlRun = $this->getFirstCrawlRun();

        foreach (['json', 'csv', 'sample', 'log'] as $type) {
            $data = $this->client->store()->downloadFileForCrawlRun(
                $crawlRun['_id'],
                $crawlRun['fields'][$type],
                $type,
            );

            $this->assertNotEmpty($data);
        }
    }

    public function testDownloadFileFromCrawlRunWithException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $crawlRun = $this->getFirstCrawlRun();

        $this->client->store()->downloadFileForCrawlRun(
            $crawlRun['_id'],
            $crawlRun['fields']['json'],
            'html',
        );
    }

    public function testUploadUrlListForExtractor(): void
    {
        $data = $this->client->store()->uploadUrlListForExtractor(
            $this->extractorId,
            ['https://example.com', 'http://example.com'],
        );

        $this->assertNotEmpty($data);
    }

    public function testDownloadUrlListFromExtractor(): void
    {
        $crawlRun = $this->getFirstCrawlRun($this->extractorId);

        $data = $this->client->store()->downloadUrlListFromExtractor(
            $this->extractorId,
            $crawlRun['fields']['urlListId'],
        );

        $this->assertNotEmpty($data);
    }

    protected function getFirstCrawlRun(?string $extractorId = null): array
    {
        static $crawlRun;

        if (null !== $crawlRun && null === $extractorId) {
            return $crawlRun;
        }

        $crawlRun = $this->client->store()->getFirstCrawlRun($extractorId);

        if (empty($crawlRun)) {
            throw new RuntimeException('There are no crawlRuns');
        }

        return $crawlRun;
    }
}
