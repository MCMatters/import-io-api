<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Tests;

/**
 * Class ExtractionTest
 *
 * @package McMatters\ImportIo\Tests
 */
class ExtractionTest extends TestCase
{
    /**
     * Test "extractorQuery" method.
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testExtractorQuery()
    {
        $data = $this->client->endpoint('extraction')->extractorQuery(
            $this->extractorId,
            'https://example.com'
        );

        $this->assertNotEmpty($data);
    }
}
