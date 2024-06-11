<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Tests;

use McMatters\ImportIo\ImportIoClient;
use PHPUnit\Framework\TestCase as BaseTestCase;

use function getenv;

use const null;

class TestCase extends BaseTestCase
{
    protected ImportIoClient $client;

    protected string $extractorId;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->client = new ImportIoClient(getenv('IMPORT_IO_API_KEY'));
        $this->extractorId = getenv('IMPORT_IO_TEST_EXTRACTOR_ID');
    }
}
