<?php

declare(strict_types=1);

namespace davekok\lalr1;

class Symbol
{
    public readonly SymbolType $type;
    public readonly string $key;
    public readonly string $name;
    public readonly int $precedence;

    public function __construct(
        SymbolType $type,
        string $key,
        string $name,
        int $precedence = 0
    ) {
        $this->type = $type;
        $this->key = $key;
        $this->name = $name;
        $this->precedence = $precedence;
    }

    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }
}
