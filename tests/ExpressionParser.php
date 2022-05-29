<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\attributes\{Parser,Rule,Input,InputOutput};
use davekok\parser\{ParserTrait,Token};

#[Parser(valueType: "int|float|null", lexar: true)]
#[InputOutput("number", "n")]
#[Input("plus", "+")]
#[Input("minus", "-")]
#[Input("times", "*", precedence: 1)]
#[Input("division", "/", precedence: 1)]
#[Input("modulo", "\\", precedence: 2)]
#[Input("leftgroup", "(")]
#[Input("rightgroup", ")")]
class ExpressionParser
{
    use ParserTrait;
    use ExpressionParserLexar;
    use ExpressionParserStitcher;

    #[Rule("n", "n + n")]
    public function add(int|float $n1, int|float $n3): int|float
    {
        return $n1 + $n3;
    }

    #[Rule("n", "n - n")]
    public function substract(int|float $n1, int|float $n3): int|float
    {
        return $n1 - $n3;
    }

    #[Rule("n", "- n")]
    public function negate(int|float $n2): int|float
    {
        return - $n2;
    }

    #[Rule("n", "n * n")]
    public function multiply(int|float $n1, int|float $n3): int|float
    {
        return $n1 * $n3;
    }

    #[Rule("n", "n / n")]
    public function divide(int|float $n1, int|float $n3): int|float
    {
        return $n1 / $n3;
    }

    #[Rule("n", "n \\ n")]
    public function modulus(int|float $n1, int|float $n3): int|float
    {
        return $n1 % $n3;
    }

    #[Rule("n", "( n )", precedence: 3)]
    public function group(int|float $n2): int|float
    {
        return $n2;
    }
}
