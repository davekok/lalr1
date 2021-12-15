<?php

declare(strict_types=1);

namespace davekok\parser\attributes;

use davekok\parser\SymbolType;

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
