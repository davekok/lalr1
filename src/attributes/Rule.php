<?php

declare(strict_types=1);

namespace davekok\lalr1\attributes;

use Attribute;

/**
 * Set this attribute on a method to declare a rule.
 *
 * Example:
 *
 *     use davekok\larl1\attributes\{Rule,Solution,Symbol,Symbols};
 *     use davekok\larl1\SymbolType;
 *
 *     #[Symbols(new Symbol(SymbolType::ROOT, "number"), new Symbol(SymbolType::LEAF, "+")]
 *     class MyRules
 *     {
 *         public function __construct(private Parser $parser) {}
 *
 *         #[Rule("number + number")]
 *         public function addRule(array $tokens): Token
 *         {
 *             return $this->parser->createToken("number", $tokens[0]->value + $tokens[2]->value);
 *         }
 *
 *         #[Solution]
 *         public function printSolution(float $number): void
 *         {
 *             echo "Solution: $number\n";
 *         }
 *     }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Rule
{
    public readonly string $text;
    public readonly int $precedence;

    public function __construct(string $text, int $precedence = 0)
    {
        $this->text = $text;
        $this->precedence = $precedence;
    }
}
