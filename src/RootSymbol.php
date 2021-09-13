<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class RootSymbol extends Symbol
{
    public function __toString(): string
    {
        return "Root:" . parent::__toString();
    }
}
