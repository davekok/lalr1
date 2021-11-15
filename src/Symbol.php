<?php

declare(strict_types=1);

namespace davekok\lalr1;

class Symbol
{
    public function __construct(
        public readonly SymbolType $type,
        public readonly string $key,
        public readonly string $name,
        public readonly int $precedence = 0,
    ) {}

    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }
}
