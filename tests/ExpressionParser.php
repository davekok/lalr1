<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\attributes\{Parser,Rule,Input,InputOutput};
use davekok\parser\ParserTrait;

#[Parser(valueType: "int|float|null", lexar: true)]
#[InputOutput("number", "/[1-9][0-9]*(\.[0-9]+)|0\.[0-9]+/")]
#[Input("plus", "'+'")]
#[Input("minus", "'-'")]
#[Input("times", "'*'", precedence: 1)]
#[Input("division", "'/'", precedence: 1)]
#[Input("modulo", "'\\'", precedence: 2)]
#[Input("leftgroup", "'('")]
#[Input("rightgroup", "')'")]
class ExpressionParser
{
    use ParserTrait;
    use ExpressionParserLexar;
    use ExpressionParserStitcher;

    #[Rule("number", "number plus number")]
    public function add(int|float $number1, int|float $number2): int|float
    {
        return $number1 + $number2;
    }

    #[Rule("number", "number minus number")]
    public function substract(int|float $number1, int|float $number2): int|float
    {
        return $number1 - $number2;
    }

    #[Rule("number", "minus number")]
    public function negate(int|float $number): int|float
    {
        return - $number;
    }

    #[Rule("number", "number times number")]
    public function multiply(int|float $number1, int|float $number2): int|float
    {
        return $number1 * $number2;
    }

    #[Rule("number", "number division number")]
    public function divide(int|float $number1, int|float $number2): int|float
    {
        return $number1 / $number2;
    }

    #[Rule("number", "number modulo number")]
    public function modulus(int|float $number1, int|float $number2): int|float
    {
        return $number1 % $number2;
    }

    #[Rule("number", "leftgroup number rightgroup", precedence: 3)]
    public function group(int|float $number): int|float
    {
        return $number;
    }
}
