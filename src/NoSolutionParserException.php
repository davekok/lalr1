<?php

declare(strict_types=1);

namespace davekok\lalr1;

use Exception;

class NoSolutionParserException extends ParserException
{
    public function __construct(string $message)
    {
        parent::__construct("End of tokens reached, but no valid solution: " . $message);
    }
}
