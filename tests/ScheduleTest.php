<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Tests;

use McMatters\ImportIo\Exceptions\ImportIoException;
use Throwable;

/**
 * Class ScheduleTest
 *
 * @package McMatters\ImportIo\Tests
 */
class ScheduleTest extends TestCase
{
    /**
     * Test "list" method.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testList()
    {
        $data = $this->client->endpoint('schedule')->list();

        $this->assertNotEmpty($data);
    }

    /**
     * Test "create" method.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testCreate()
    {
        $interval = '15 * * * *';

        $data = $this->client->endpoint('schedule')->create($this->extractorId, $interval);

        $this->assertNotEmpty($data);
        $this->assertSame($this->extractorId, $data['extractorId']);
        $this->assertSame($interval, $data['interval']);
    }

    /**
     * Test "getByExtractorId" method.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testGetByExtractorId()
    {
        $data = $this->client->endpoint('schedule')->getByExtractorId($this->extractorId);

        $this->assertNotEmpty($data);
        $this->assertSame($this->extractorId, $data['extractorId']);
    }

    /**
     * Test "getByExtractorId" with creating.
     */
    public function testGetByExtractorIdWithCreating()
    {
        // Remove schedule before.
        try {
            $this->client->endpoint('schedule')->delete($this->extractorId);
        } catch (Throwable $e) {
            //
        }

        $interval = '15 * * * *';

        $this->client->endpoint('schedule')->create($this->extractorId, $interval);
        $data = $this->client->endpoint('schedule')->getByExtractorId($this->extractorId);

        $this->assertSame($this->extractorId, $data['extractorId']);
        $this->assertSame($interval, $data['interval']);
    }

    /**
     * Test "delete" method.
     */
    public function testDelete()
    {
        $code = $this->client->endpoint('schedule')->delete($this->extractorId);

        $this->assertSame(200, $code);
    }

    /**
     * Test "delete" method with getting exception.
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function testDeleteWithException()
    {
        $this->expectException(ImportIoException::class);

        $this->client->endpoint('schedule')->delete($this->extractorId);
        $this->client->endpoint('schedule')->delete($this->extractorId);
    }
}
