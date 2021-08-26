<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

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
     * @throws \InvalidArgumentException
     * @throws \Throwable
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
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function testStartCrawlException()
    {
        $this->expectException(Throwable::class);

        $this->client->run()->startCrawl($this->extractorId);
        $this->client->run()->startCrawl($this->extractorId);
        $this->client->run()->cancelCrawl($this->extractorId);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function testCancelCrawlException()
    {
        $this->expectException(Throwable::class);

        $this->client->run()->cancelCrawl($this->extractorId);
        $this->client->run()->cancelCrawl($this->extractorId);
    }
}
