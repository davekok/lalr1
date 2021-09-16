<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class Token
{
    public function __construct(
        public readonly Symbol $symbol,
        public mixed $value = null
    ) {}

    public function __toString(): string
    {
        return $this->symbol . ":" . var_export($this->value, true);
    }
}
