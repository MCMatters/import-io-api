<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Tests;

/**
 * Class RssTest
 *
 * @package McMatters\ImportIo\Tests
 */
class RssTest extends TestCase
{
    /**
     * Test "getRuns" method.
     */
    public function testGetRuns()
    {
        $data = $this->importIo->rss->getRuns($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * Test "getRunsGuids" method.
     */
    public function testGetRunsGuids()
    {
        $data = $this->importIo->rss->getRunsGuids($this->extractorId);

        $this->assertTrue(is_array($data));
    }
}
