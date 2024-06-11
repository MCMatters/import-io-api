<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

use Throwable;

class ScheduleTest extends TestCase
{
    public function testList(): void
    {
        $data = $this->client->schedule()->list();

        $this->assertNotEmpty($data);
    }

    public function testGetByExtractorIdWithCreating(): void
    {
        // Remove schedule before.
        try {
            $this->client->schedule()->delete($this->extractorId);
        } catch (Throwable) {
            //
        }

        $interval = '15 * * * *';

        $this->client->schedule()->create($this->extractorId, $interval);
        $data = $this->client->schedule()->getByExtractorId($this->extractorId);

        $this->assertSame($this->extractorId, $data['extractorId']);
        $this->assertSame($interval, $data['interval']);
    }

    public function testDelete(): void
    {
        $code = $this->client->schedule()->delete($this->extractorId);

        $this->assertSame(200, $code);
    }

    public function testDeleteWithException(): void
    {
        $this->expectException(Throwable::class);

        $this->client->schedule()->delete($this->extractorId);
        $this->client->schedule()->delete($this->extractorId);
    }
}
