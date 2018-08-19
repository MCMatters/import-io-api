<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Exceptions;

use Exception;

/**
 * Class ImportIoException
 *
 * @package McMatters\ImportIo\Exceptions
 */
class ImportIoException extends Exception
{
    /**
     * ImportIoException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
