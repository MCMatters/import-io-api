<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Tests;

use InvalidArgumentException;

/**
 * Class DataTest
 *
 * @package McMatters\ImportIo\Tests
 */
class DataTest extends TestCase
{
    /**
     * Test "getLatestData" method with getting json.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testGetLatestDataJson()
    {
        $data = $this->client->endpoint('data')->getLatestData($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * Test "getLatestData" method with getting csv string.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testGetLatestDataCsv()
    {
        $data = $this->client->endpoint('data')->getLatestData($this->extractorId, 'csv');

        $this->assertNotEmpty($data);
    }

    /**
     * Test "getLatestData" method with getting exception.
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function testGetLatestDataWithWrongType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client->endpoint('data')->getLatestData($this->extractorId, 'xls');
    }

    /**
     * Test "getLatestData" method with getting exception.
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \Exception
     */
    public function testGetLatestDataWithWrongExtractorId()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client->endpoint('data')->getLatestData(md5(random_bytes(12)));
    }
}
