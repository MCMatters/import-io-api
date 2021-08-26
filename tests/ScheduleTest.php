<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

use Throwable;

/**
 * Class ScheduleTest
 *
 * @package McMatters\ImportIo\Tests
 */
class ScheduleTest extends TestCase
{
    /**
     * @throws \Throwable
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testList()
    {
        $data = $this->client->schedule()->list();

        $this->assertNotEmpty($data);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Throwable
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
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testDelete()
    {
        $code = $this->client->schedule()->delete($this->extractorId);

        $this->assertSame(200, $code);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function testDeleteWithException()
    {
        $this->expectException(Throwable::class);

        $this->client->schedule()->delete($this->extractorId);
        $this->client->schedule()->delete($this->extractorId);
    }
}
