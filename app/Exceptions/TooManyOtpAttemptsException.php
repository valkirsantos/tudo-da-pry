<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class TooManyOtpAttemptsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Muitas tentativas. Solicite um novo código.');
    }
}
