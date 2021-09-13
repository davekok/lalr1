<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class LeafSymbol extends Symbol
{
    public function __toString(): string
    {
        return "Leaf:" . parent::__toString();
    }
}
