<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

use InvalidArgumentException;

use function md5, random_bytes;

/**
 * Class DataTest
 *
 * @package McMatters\ImportIo\Tests
 */
class DataTest extends TestCase
{
    /**
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetLatestDataJson()
    {
        $data = $this->client->data()->getLatestData($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetLatestDataCsv()
    {
        $data = $this->client->data()->getLatestData($this->extractorId, 'csv');

        $this->assertNotEmpty($data);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function testGetLatestDataWithWrongType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client->data()->getLatestData($this->extractorId, 'xls');
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function testGetLatestDataWithWrongExtractorId()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client->data()->getLatestData(md5(random_bytes(12)));
    }
}
