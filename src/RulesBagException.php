<?php

declare(strict_types=1);

namespace davekok\parser;

class RulesBagException extends ParserException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
