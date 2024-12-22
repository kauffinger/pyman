<?php

declare(strict_types=1);

namespace Kauffinger\Pyman\Exceptions;

use Exception;

class PymanException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
