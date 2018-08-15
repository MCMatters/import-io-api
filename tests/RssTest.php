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
     * @throws \InvalidArgumentException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetRuns()
    {
        $data = $this->client->rss()->getRuns($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetRunsGuids()
    {
        $data = $this->client->rss()->getRunsGuids($this->extractorId);

        $this->assertInternalType('array', $data);
    }
}
