<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Utilities;

use Throwable;

use function usleep;

use const null;

class Retry
{
    public function __construct(
        protected int $attempts,
        protected int $sleepMilliseconds = 0,
    ) {
    }

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

                if ($this->sleepMilliseconds) {
                    usleep($this->sleepMilliseconds * 1000);
                }
            }
        } while ($attempts > 0);

        return null;
    }
}
