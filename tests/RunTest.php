<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

use Throwable;

class RunTest extends TestCase
{
    /**
     * Cancel all crawls after testing.
     */
    public function __destruct()
    {
        try {
            $this->client->run()->cancelCrawl($this->extractorId);
        } catch (Throwable) {
            // There is no ran crawl.
        }
    }

    public function testStartAndCancelCrawl(): void
    {
        $startCrawlId = $this->client->run()->startCrawl($this->extractorId);
        $cancelCrawlId = $this->client->run()->cancelCrawl($this->extractorId);

        $this->assertNotEmpty($startCrawlId);
        $this->assertNotEmpty($cancelCrawlId);
        $this->assertSame($startCrawlId, $cancelCrawlId);
    }

    public function testStartCrawlException(): void
    {
        $this->expectException(Throwable::class);

        $this->client->run()->startCrawl($this->extractorId);
        $this->client->run()->startCrawl($this->extractorId);
        $this->client->run()->cancelCrawl($this->extractorId);
    }

    public function testCancelCrawlException(): void
    {
        $this->expectException(Throwable::class);

        $this->client->run()->cancelCrawl($this->extractorId);
        $this->client->run()->cancelCrawl($this->extractorId);
    }
}
