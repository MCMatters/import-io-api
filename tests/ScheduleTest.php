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
     */
    public function testList()
    {
        $data = $this->importIo->schedule->list();

        $this->assertNotEmpty($data);
    }

    /**
     * Test "create" method.
     */
    public function testCreate()
    {
        $interval = '15 * * * *';

        $data = $this->importIo->schedule->create($this->extractorId, $interval);

        $this->assertNotEmpty($data);
        $this->assertSame($this->extractorId, $data['extractorId']);
        $this->assertSame($interval, $data['interval']);
    }

    /**
     * Test "getByExtractorId" method.
     */
    public function testGetByExtractorId()
    {
        $data = $this->importIo->schedule->getByExtractorId($this->extractorId);

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
            $this->importIo->schedule->delete($this->extractorId);
        } catch (Throwable $e) {
            //
        }

        $interval = '15 * * * *';

        $this->importIo->schedule->create($this->extractorId, $interval);
        $data = $this->importIo->schedule->getByExtractorId($this->extractorId);

        $this->assertSame($this->extractorId, $data['extractorId']);
        $this->assertSame($interval, $data['interval']);
    }

    /**
     * Test "delete" method.
     */
    public function testDelete()
    {
        $code = $this->importIo->schedule->delete($this->extractorId);

        $this->assertSame(200, $code);
    }

    /**
     * Test "delete" method with getting exception.
     */
    public function testDeleteWithException()
    {
        $this->expectException(ImportIoException::class);

        $this->importIo->schedule->delete($this->extractorId);
        $this->importIo->schedule->delete($this->extractorId);
    }
}
