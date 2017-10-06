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
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
