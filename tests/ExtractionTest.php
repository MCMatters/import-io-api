<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

class ExtractionTest extends TestCase
{
    public function testExtractorQuery(): void
    {
        $data = $this->client->extraction()->extractorQuery(
            $this->extractorId,
            'https://example.com',
        );

        $this->assertNotEmpty($data);
    }
}
