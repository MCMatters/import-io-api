<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Tests;

use McMatters\ImportIo\Exceptions\ImportIoException;
use Throwable;

/**
 * Class RunTest
 *
 * @package McMatters\ImportIo\Tests
 */
class RunTest extends TestCase
{
    /**
     * Cancel all crawls after testing.
     */
    public function __destruct()
    {
        try {
            $this->client->run()->cancelCrawl($this->extractorId);
        } catch (Throwable $e) {
            // There is no ran crawl.
        }
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testStartAndCancelCrawl()
    {
        $startCrawlId = $this->client->run()->startCrawl($this->extractorId);
        $cancelCrawlId = $this->client->run()->cancelCrawl($this->extractorId);

        $this->assertNotEmpty($startCrawlId);
        $this->assertNotEmpty($cancelCrawlId);
        $this->assertSame($startCrawlId, $cancelCrawlId);
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     */
    public function testStartCrawlException()
    {
        $this->expectException(ImportIoException::class);

        $this->client->run()->startCrawl($this->extractorId);
        $this->client->run()->startCrawl($this->extractorId);
        $this->client->run()->cancelCrawl($this->extractorId);
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     */
    public function testCancelCrawlException()
    {
        $this->expectException(ImportIoException::class);

        $this->client->run()->cancelCrawl($this->extractorId);
        $this->client->run()->cancelCrawl($this->extractorId);
    }
}
