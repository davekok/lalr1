<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class BranchSymbol extends Symbol
{
    public function __toString(): string
    {
        return "Branch:" . parent::__toString();
    }
}
