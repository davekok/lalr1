<?php

declare(strict_types=1);

namespace davekok\lalr1;

use Exception;

class RulesBagException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
