<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

use InvalidArgumentException;

use function md5;
use function random_bytes;

/**
 * Class DataTest
 *
 * @package McMatters\ImportIo\Tests
 */
class DataTest extends TestCase
{
    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testGetLatestDataJson(): void
    {
        $data = $this->client->data()->getLatestData($this->extractorId);

        $this->assertNotEmpty($data);
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testGetLatestDataCsv(): void
    {
        $data = $this->client->data()->getLatestData($this->extractorId, 'csv');

        $this->assertNotEmpty($data);
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testGetLatestDataWithWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client->data()->getLatestData($this->extractorId, 'xls');
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testGetLatestDataWithWrongExtractorId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client->data()->getLatestData(md5(random_bytes(12)));
    }
}
