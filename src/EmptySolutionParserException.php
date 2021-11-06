<?php

declare(strict_types=1);

namespace davekok\lalr1;

use Exception;

class EmptySolutionParserException extends ParserException
{
    public function __construct()
    {
        parent::__construct("No tokens pushed");
    }
}
