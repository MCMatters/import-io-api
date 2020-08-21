<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Utilities;

use Throwable;

use function sleep;

use const null;

/**
 * Class Retry
 *
 * @package McMatters\ImportIo\Utilities
 */
class Retry
{
    /**
     * @var int
     */
    protected $attempts;

    /**
     * @var int
     */
    protected $sleep;

    /**
     * Retry constructor.
     *
     * @param int $attempts
     * @param int $sleep
     */
    public function __construct(int $attempts, int $sleep = 0)
    {
        $this->attempts = $attempts;
        $this->sleep = $sleep;
    }

    /**
     * @param callable $callback
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function run(callable $callback)
    {
        $attempts = $this->attempts;

        do {
            try {
                return $callback();
            } catch (Throwable $e) {
                $attempts--;

                if (!$attempts) {
                    throw $e;
                }

                if ($this->sleep) {
                    sleep($this->sleep);
                }
            }
        } while ($attempts > 0);

        return null;
    }
}
