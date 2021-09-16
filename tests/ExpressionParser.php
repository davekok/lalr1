<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\BranchSymbol;
use DaveKok\LALR1\LeafSymbol;
use DaveKok\LALR1\ParserFactoryInterface;
use DaveKok\LALR1\ParserInterface;
use DaveKok\LALR1\RootSymbol;
use DaveKok\LALR1\Rule;
use DaveKok\LALR1\Symbols;
use DaveKok\LALR1\Token;
use Exception;
use stdClass;

#[Symbols(
    new RootSymbol("number"),
    new LeafSymbol("+"),
    new LeafSymbol("-"),
    new LeafSymbol("*", 1),
    new LeafSymbol("/", 1),
    new LeafSymbol("\\", 2),
    new LeafSymbol("("),
    new LeafSymbol(")"),
)]
class ExpressionParser
{
    public readonly ParserInterface $parser;

    public function __construct(ParserFactoryInterface $factory, bool $debug = false)
    {
        $this->parser = $factory->createParser($this, $debug);
    }

    public function parse(string $buffer): mixed
    {
        try {
            foreach (new ExpressionScanner($this->parser, $buffer) as $token) {
                $this->parser->pushToken($token);
            }
            return $this->parser->endOfTokens();
        } catch (\Throwable $e) {
            echo "\n";
            print_r($this->parser->getDebugLog());
            throw $e;
        }
    }

    #[Rule("number + number")]
    public function add(Token $leftNumber, Token $plus, Token $rightNumber): Token
    {
        return $this->parser->createToken("number", $leftNumber->value + $rightNumber->value);
    }

    #[Rule("number - number")]
    public function substract(Token $leftNumber, Token $minus, Token $rightNumber): Token
    {
        return $this->parser->createToken("number", $leftNumber->value - $rightNumber->value);
    }

    #[Rule("- number")]
    public function negate(Token $minus, Token $number): Token
    {
        return $this->parser->createToken("number", - $number->value);
    }

    #[Rule("number * number")]
    public function multiply(Token $leftNumber, Token $asterix, Token $rightNumber): Token
    {
        return $this->parser->createToken("number", $leftNumber->value * $rightNumber->value);
    }

    #[Rule("number / number")]
    public function divide(Token $leftNumber, Token $slash, Token $rightNumber): Token
    {
        return $this->parser->createToken("number", $leftNumber->value / $rightNumber->value);
    }

    #[Rule("number \\ number")]
    public function modulus(Token $leftNumber, Token $backSlash, Token $rightNumber): Token
    {
        return $this->parser->createToken("number", $leftNumber->value % $rightNumber->value);
    }

    #[Rule("( number )", precedence: 3)]
    public function group(Token $left, Token $number, Token $right): Token
    {
        return $number;
    }
}
