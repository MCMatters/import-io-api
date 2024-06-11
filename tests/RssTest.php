<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

class RssTest extends TestCase
{
    public function testGetRuns(): void
    {
        $data = $this->client->rss()->getRuns($this->extractorId);

        $this->assertNotEmpty($data);
    }

    public function testGetRunsGuids(): void
    {
        $data = $this->client->rss()->getRunsGuids($this->extractorId);

        $this->assertIsArray($data);
    }
}
