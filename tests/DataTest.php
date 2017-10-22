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
     */
    public function testGetLatestDataJson()
    {
        $data = $this->importIo->data->getLatestData($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * Test "getLatestData" method with getting csv string.
     */
    public function testGetLatestDataCsv()
    {
        $data = $this->importIo->data->getLatestData($this->extractorId, 'csv');

        $this->assertNotEmpty($data);
    }

    /**
     * Test "getLatestData" method with getting exception.
     */
    public function testGetLatestDataWithWrongType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->importIo->data->getLatestData($this->extractorId, 'xls');
    }

    /**
     * Test "getLatestData" method with getting exception.
     */
    public function testGetLatestDataWithWrongExtractorId()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->importIo->data->getLatestData(md5(random_bytes(12)));
    }
}
