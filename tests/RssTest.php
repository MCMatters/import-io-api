<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

/**
 * Class RssTest
 *
 * @package McMatters\ImportIo\Tests
 */
class RssTest extends TestCase
{
    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testGetRuns(): void
    {
        $data = $this->client->rss()->getRuns($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testGetRunsGuids(): void
    {
        $data = $this->client->rss()->getRunsGuids($this->extractorId);

        $this->assertIsArray($data);
    }
}
