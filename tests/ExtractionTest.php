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
     */
    public function testExtractorQuery()
    {
        $data = $this->importIo->extraction->extractorQuery(
            $this->extractorId,
            'https://example.com'
        );

        $this->assertNotEmpty($data['content']);
        $this->assertNotEmpty($data['headers']);
        $this->assertSame(200, $data['code']);
    }
}
