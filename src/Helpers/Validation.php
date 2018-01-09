<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Helpers;

use InvalidArgumentException;
use const true;
use function in_array, preg_match;

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
     * @throws InvalidArgumentException
     */
    public static function checkExtractorId(string $extractorId)
    {
        self::checkUuid($extractorId, 'extractorId');
    }

    /**
     * @param string $crawlRunId
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkCrawlRunId(string $crawlRunId)
    {
        self::checkUuid($crawlRunId, 'crawlRunId');
    }

    /**
     * @param string $attachmentId
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkAttachmentId(string $attachmentId)
    {
        self::checkUuid($attachmentId, 'attachmentId');
    }

    /**
     * @param string $reportId
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkReportId(string $reportId)
    {
        self::checkUuid($reportId, 'reportId');
    }

    /**
     * @param string $reportRunId
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkReportRunId(string $reportRunId)
    {
        self::checkUuid($reportRunId, 'reportRunId');
    }

    /**
     * @param string $uuid
     * @param string $name
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkUuid(string $uuid, string $name)
    {
        $check = preg_match(
            '/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/',
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
     * @throws InvalidArgumentException
     */
    public static function checkDataType(string $type)
    {
        self::checkType($type, ['json', 'csv']);
    }

    /**
     * @param string $type
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkDownloadableCrawlRunType(string $type = 'json')
    {
        self::checkType($type, ['json', 'csv', 'log', 'sample', 'files']);
    }

    /**
     * @param string $type
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkDownloadableReportRunType(string $type = 'json')
    {
        self::checkType($type, ['json', 'csv', 'pdf', 'xlsx']);
    }

    /**
     * @param string $type
     * @param array $types
     *
     * @throws InvalidArgumentException
     */
    public static function checkType(string $type, array $types)
    {
        if (!in_array($type, $types, true)) {
            throw new InvalidArgumentException('Incompatible type was passed');
        }
    }

    /**
     * @param string $interval
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function checkScheduleInterval(string $interval)
    {
        if (!preg_match('/^[\s\d\*\/,-]+$/', $interval)) {
            throw new InvalidArgumentException('Invalid interval was passed');
        }
    }
}
