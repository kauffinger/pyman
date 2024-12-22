<?php

declare(strict_types=1);

namespace Kauffinger\Pyman\Exceptions;

class VenvManagementException extends PymanException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
