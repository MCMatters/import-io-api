<?php

declare(strict_types=1);

namespace McMatters\ImportIo;

use InvalidArgumentException;

use function is_array;
use function is_string;
use function json_decode;

use const true;

/**
 * Class ImportIoWebhook
 *
 * @package McMatters\ImportIo
 */
class ImportIoWebhook
{
    /**
     * @var array
     */
    protected $data;

    /**
     * ImportIoWebhook constructor.
     *
     * @param array|string $data
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be as a string or an array');
        }

        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->data['state'];
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->getState() === 'FINISHED';
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        return (int) ($this->data['rowCount'] ?? 0);
    }

    /**
     * @return int
     */
    public function getTotalUrlCount(): int
    {
        return (int) ($this->data['totalUrlCount'] ?? 0);
    }

    /**
     * @return int
     */
    public function getSuccessUrlCount(): int
    {
        return (int) ($this->data['successUrlCount'] ?? 0);
    }

    /**
     * @return int
     */
    public function getFailureUrlCount(): int
    {
        return (int) ($this->data['failureUrlCount'] ?? 0);
    }

    /**
     * @return string
     */
    public function getExtractorId(): string
    {
        return $this->data['extractorId'];
    }

    /**
     * @return array
     */
    public function raw(): array
    {
        return $this->data;
    }
}
