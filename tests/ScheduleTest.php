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
     * @return void
     *
     * @throws \Throwable
     */
    public function testList(): void
    {
        $data = $this->client->schedule()->list();

        $this->assertNotEmpty($data);
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testGetByExtractorIdWithCreating(): void
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
     * @return void
     *
     * @throws \Throwable
     */
    public function testDelete(): void
    {
        $code = $this->client->schedule()->delete($this->extractorId);

        $this->assertSame(200, $code);
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testDeleteWithException(): void
    {
        $this->expectException(Throwable::class);

        $this->client->schedule()->delete($this->extractorId);
        $this->client->schedule()->delete($this->extractorId);
    }
}
