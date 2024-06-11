<?php

declare(strict_types=1);

namespace McMatters\ImportIo;

use InvalidArgumentException;

use McMatters\ImportIo\Endpoints\Store;

use function is_array;
use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;
use const true;

class ImportIoWebhook
{
    protected array $data;

    public function __construct(array|string $data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be as a string or an array');
        }

        $this->data = $data;
    }

    public function getState(): string
    {
        return $this->data['state'];
    }

    public function isFinished(): bool
    {
        return $this->getState() === Store::STATE_FINISHED;
    }

    public function getRowCount(): int
    {
        return (int) ($this->data['rowCount'] ?? 0);
    }

    public function getTotalUrlCount(): int
    {
        return (int) ($this->data['totalUrlCount'] ?? 0);
    }

    public function getSuccessUrlCount(): int
    {
        return (int) ($this->data['successUrlCount'] ?? 0);
    }

    public function getFailureUrlCount(): int
    {
        return (int) ($this->data['failureUrlCount'] ?? 0);
    }

    public function getExtractorId(): string
    {
        return $this->data['extractorId'];
    }

    public function raw(): array
    {
        return $this->data;
    }
}
