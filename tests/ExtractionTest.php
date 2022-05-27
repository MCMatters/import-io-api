<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Tests;

/**
 * Class ExtractionTest
 *
 * @package McMatters\ImportIo\Tests
 */
class ExtractionTest extends TestCase
{
    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function testExtractorQuery(): void
    {
        $data = $this->client->extraction()->extractorQuery(
            $this->extractorId,
            'https://example.com',
        );

        $this->assertNotEmpty($data);
    }
}
