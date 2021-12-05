<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\attributes\{Rule,Symbol,Symbols};
use davekok\lalr1\{Parser,Rules,SymbolType,Token};
use Exception;
use stdClass;

#[Symbols(
    new Symbol(SymbolType::ROOT, "number"),
    new Symbol(SymbolType::LEAF, "+"),
    new Symbol(SymbolType::LEAF, "-"),
    new Symbol(SymbolType::LEAF, "*", 1),
    new Symbol(SymbolType::LEAF, "/", 1),
    new Symbol(SymbolType::LEAF, "\\", 2),
    new Symbol(SymbolType::LEAF, "("),
    new Symbol(SymbolType::LEAF, ")"),
)]
class ExpressionRules implements Rules
{
    private readonly Parser $parser;

    public function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    #[Rule("number + number")]
    public function add(array $tokens): Token
    {
        return $this->parser->createToken("number", $tokens[0]->value + $tokens[2]->value);
    }

    #[Rule("number - number")]
    public function substract(array $tokens): Token
    {
        return $this->parser->createToken("number", $tokens[0]->value - $tokens[2]->value);
    }

    #[Rule("- number")]
    public function negate(array $tokens): Token
    {
        return $this->parser->createToken("number", - $tokens[1]->value);
    }

    #[Rule("number * number")]
    public function multiply(array $tokens): Token
    {
        return $this->parser->createToken("number", $tokens[0]->value * $tokens[2]->value);
    }

    #[Rule("number / number")]
    public function divide(array $tokens): Token
    {
        return $this->parser->createToken("number", $tokens[0]->value / $tokens[2]->value);
    }

    #[Rule("number \\ number")]
    public function modulus(array $tokens): Token
    {
        return $this->parser->createToken("number", $tokens[0]->value % $tokens[2]->value);
    }

    #[Rule("( number )", precedence: 3)]
    public function group(array $tokens): Token
    {
        return $tokens[1];
    }
}
