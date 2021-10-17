<?php

declare(strict_types=1);

namespace davekok\lalr1\attributes;

use Attribute;

/**
 * Set this attribute on a class to declare the symbols in use.
 *
 * Example:
 *
 *     use davekok\larl1\attributes\{Symbol,Symbols};
 *     use davekok\larl1\SymbolType;
 *
 *     #[Symbols(
 *         new Symbol(SymbolType::ROOT, "rootSymbol"),
 *         new Symbol(SymbolType::BRANCH, "branchSymbol"),
 *         new Symbol(SymbolType::LEAF, "leafSymbol")
 *     )]
 *     class MyRules
 *     {
 *     }
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Symbols
{
    public readonly array $symbols;

    public function __construct(Symbol ...$symbols)
    {
        $this->symbols = $symbols;
    }
}
