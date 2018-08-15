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
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testList()
    {
        $data = $this->client->schedule()->list();

        $this->assertNotEmpty($data);
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testCreate()
    {
        $interval = '15 * * * *';

        $data = $this->client->schedule()->create($this->extractorId, $interval);

        $this->assertNotEmpty($data);
        $this->assertSame($this->extractorId, $data['extractorId']);
        $this->assertSame($interval, $data['interval']);
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetByExtractorId()
    {
        $data = $this->client->schedule()->getByExtractorId($this->extractorId);

        $this->assertNotEmpty($data);
        $this->assertSame($this->extractorId, $data['extractorId']);
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetByExtractorIdWithCreating()
    {
        // Remove schedule before.
        try {
            $this->client->schedule()->delete($this->extractorId);
        } catch (Throwable $e) {
            //
        }

        $interval = '15 * * * *';

        $this->client->schedule()->create($this->extractorId, $interval);
        $data = $this->client->schedule()->getByExtractorId($this->extractorId);

        $this->assertSame($this->extractorId, $data['extractorId']);
        $this->assertSame($interval, $data['interval']);
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testDelete()
    {
        $code = $this->client->schedule()->delete($this->extractorId);

        $this->assertSame(200, $code);
    }

    /**
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \InvalidArgumentException
     */
    public function testDeleteWithException()
    {
        $this->expectException(ImportIoException::class);

        $this->client->schedule()->delete($this->extractorId);
        $this->client->schedule()->delete($this->extractorId);
    }
}
