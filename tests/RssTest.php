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
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testGetRuns()
    {
        $data = $this->client->endpoint('rss')->getRuns($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * Test "getRunsGuids" method.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testGetRunsGuids()
    {
        $data = $this->client->endpoint('rss')->getRunsGuids($this->extractorId);

        $this->assertTrue(is_array($data));
    }
}
