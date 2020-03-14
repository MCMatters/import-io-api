<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Helpers;

use InvalidArgumentException;

use function in_array, preg_match;

use const true;

/**
 * Class Validation
 *
 * @package McMatters\ImportIo\Helpers
 */
class Validation
{
    /**
     * @param string $extractorId
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkExtractorId(string $extractorId): void
    {
        self::checkUuid($extractorId, 'extractorId');
    }

    /**
     * @param string $crawlRunId
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkCrawlRunId(string $crawlRunId): void
    {
        self::checkUuid($crawlRunId, 'crawlRunId');
    }

    /**
     * @param string $attachmentId
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkAttachmentId(string $attachmentId): void
    {
        self::checkUuid($attachmentId, 'attachmentId');
    }

    /**
     * @param string $reportId
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkReportId(string $reportId): void
    {
        self::checkUuid($reportId, 'reportId');
    }

    /**
     * @param string $reportRunId
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkReportRunId(string $reportRunId): void
    {
        self::checkUuid($reportRunId, 'reportRunId');
    }

    /**
     * @param string $uuid
     * @param string $name
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkUuid(string $uuid, string $name): void
    {
        $check = preg_match(
            '/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/i',
            $uuid
        );

        if (!$check) {
            throw new InvalidArgumentException("Invalid {$name} was passed");
        }
    }

    /**
     * @param string $type
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkDataType(string $type): void
    {
        self::checkType($type, ['json', 'csv']);
    }

    /**
     * @param string $type
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkDownloadableCrawlRunType(string $type = 'json'): void
    {
        self::checkType($type, ['json', 'csv', 'log', 'sample', 'files']);
    }

    /**
     * @param string $type
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkDownloadableReportRunType(string $type = 'json'): void
    {
        self::checkType($type, ['json', 'csv', 'pdf', 'xlsx']);
    }

    /**
     * @param string $type
     * @param array $types
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkType(string $type, array $types): void
    {
        if (!in_array($type, $types, true)) {
            throw new InvalidArgumentException('Incompatible type was passed');
        }
    }

    /**
     * @param string $interval
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkScheduleInterval(string $interval): void
    {
        if (!preg_match('/^[\s\d\*\/,-]+$/', $interval)) {
            throw new InvalidArgumentException('Invalid interval was passed');
        }
    }
}
