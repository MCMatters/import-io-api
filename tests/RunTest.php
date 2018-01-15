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
            $this->client->endpoint('run')->cancelCrawl($this->extractorId);
        } catch (Throwable $e) {
            // There is no ran crawl.
        }
    }

    /**
     * Test "startCrawl" method.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testStartAndCancelCrawl()
    {
        $startCrawlId = $this->client->endpoint('run')->startCrawl($this->extractorId);
        $cancelCrawlId = $this->client->endpoint('run')->cancelCrawl($this->extractorId);

        $this->assertNotEmpty($startCrawlId);
        $this->assertNotEmpty($cancelCrawlId);
        $this->assertSame($startCrawlId, $cancelCrawlId);
    }

    /**
     * Test "startCrawl" method with getting exception.
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function testStartCrawlException()
    {
        $this->expectException(ImportIoException::class);

        $this->client->endpoint('run')->startCrawl($this->extractorId);
        $this->client->endpoint('run')->startCrawl($this->extractorId);
        $this->client->endpoint('run')->cancelCrawl($this->extractorId);
    }

    /**
     * Test "cancelCrawl" method with getting exception.
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function testCancelCrawlException()
    {
        $this->expectException(ImportIoException::class);

        $this->client->endpoint('run')->cancelCrawl($this->extractorId);
        $this->client->endpoint('run')->cancelCrawl($this->extractorId);
    }
}
