<?php

declare(strict_types=1);

namespace davekok\lalr1;

class Token
{
    public readonly Symbol $symbol;
    public mixed $value;

    public function __construct(Symbol $symbol, mixed $value = null)
    {
        $this->symbol = $symbol;
        $this->value  = $value;
    }

    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }
}
