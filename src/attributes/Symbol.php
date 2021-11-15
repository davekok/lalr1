<?php

declare(strict_types=1);

namespace davekok\lalr1\attributes;

use davekok\lalr1\SymbolType;

/**
 * Create a Symbol used with the Symbols attribute.
 */
class Symbol
{
    public function __construct(
        public readonly SymbolType $type,
        public readonly string $name,
        public readonly int $precedence = 0
    ) {}
}
