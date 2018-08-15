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
     * @throws \InvalidArgumentException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSearchCrawlRuns()
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
            ['_page' => 1, '_perpage' => 1]
        );

        $this->assertNotEmpty($data);

        // With everything except sortBy.
        $data = $this->client->store()->searchCrawlRuns(
            $this->extractorId,
            ['_page' => 1, '_perpage' => 1]
        );

        $this->assertNotEmpty($data);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetCrawlRunProgress()
    {
        $crawlRun = $this->getFirstCrawlRun();

        $data = $this->client->store()->getCrawlRunProgress($crawlRun['_id']);

        $this->assertNotEmpty($data);
        $this->assertSame($crawlRun['_id'], $data['guid']);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testDownloadFileForCrawlRun()
    {
        $crawlRun = $this->getFirstCrawlRun();

        foreach (['json', 'csv', 'sample', 'log'] as $type) {
            $data = $this->client->store()->downloadFileForCrawlRun(
                $crawlRun['_id'],
                $crawlRun['fields'][$type],
                $type
            );

            $this->assertNotEmpty($data);
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     */
    public function testDownloadFileFromCrawlRunWithException()
    {
        $this->expectException(InvalidArgumentException::class);

        $crawlRun = $this->getFirstCrawlRun();

        $this->client->store()->downloadFileForCrawlRun(
            $crawlRun['_id'],
            $crawlRun['fields']['json'],
            'html'
        );
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testUploadUrlListForExtractor()
    {
        $data = $this->client->store()->uploadUrlListForExtractor(
            $this->extractorId,
            ['https://example.com', 'http://example.com']
        );

        $this->assertNotEmpty($data);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testDownloadUrlListFromExtractor()
    {
        $crawlRun = $this->getFirstCrawlRun($this->extractorId);

        $data = $this->client->store()->downloadUrlListFromExtractor(
            $this->extractorId,
            $crawlRun['fields']['urlListId']
        );

        $this->assertNotEmpty($data);
    }

    /**
     * @param string|null $extractorId
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     */
    protected function getFirstCrawlRun(string $extractorId = null): array
    {
        static $crawlRun;

        if (null !== $crawlRun && null === $extractorId) {
            return $crawlRun;
        }

        $crawlRun = $this->client->store()->getFirstCrawlRun($extractorId);

        if (empty($crawlRun)) {
            throw new RuntimeException('There is no crawlRuns');
        }

        return $crawlRun;
    }
}
