<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use Exception;

class ParserException extends Exception
{
    public function __construct(
        string $message
    )
    {
        parent::__construct($message);
    }
}
