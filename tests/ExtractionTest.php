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
     * @throws \InvalidArgumentException
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testExtractorQuery()
    {
        $data = $this->client->extraction()->extractorQuery(
            $this->extractorId,
            'https://example.com'
        );

        $this->assertNotEmpty($data);
    }
}
