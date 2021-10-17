<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\{Parser,Rules,RulesFactory};
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \davekok\lalr1\Parser::__construct
 * @covers \davekok\lalr1\Parser::createToken
 * @covers \davekok\lalr1\Parser::endOfTokens
 * @covers \davekok\lalr1\Parser::pushToken
 * @covers \davekok\lalr1\Parser::reduce
 * @covers \davekok\lalr1\Parser::reset
 * @covers \davekok\lalr1\Parser::setRulesObject
 * @uses \davekok\lalr1\attributes\Rule
 * @uses \davekok\lalr1\attributes\Solution
 * @uses \davekok\lalr1\attributes\Symbol
 * @uses \davekok\lalr1\attributes\Symbols
 * @uses \davekok\lalr1\Key
 * @uses \davekok\lalr1\Rule
 * @uses \davekok\lalr1\Rules
 * @uses \davekok\lalr1\RulesFactory
 * @uses \davekok\lalr1\Symbol
 * @uses \davekok\lalr1\Token
 * @uses \davekok\lalr1\Tokens
 */
class ExpressionTest extends TestCase
{
    public function simpleData(): array
    {
        return [
            [9384,        "9384"               ],
            [9384.38437,  "9384.38437         "],
            [13,          "8 + 5              "],
            [3,           "8 - 5              "],
            [40,          "8 * 5              "],
            [1.6,         "8 / 5              "],
            [3,           "8 \\ 5             "],
            [8,           "(8)                "],
            [43,          "8 * 5 + 3          "],
            [43,          "3 + 8 * 5          "],
            [43,          "3 + (8 * 5)        "],
            [55,          "(3 + 8) * 5        "],
            [41,          "3 + 8 * 5 - 2      "],
            [42,          "3 + 8 * 5 - 2 / 2  "],
            [20.5,        "(3 + 8 * 5 - 2) / 2"],
        ];
    }

    /**
     * @dataProvider simpleData
     */
    public function testSimple(mixed $expected, string $expression): void
    {
        $parser  = new Parser((new RulesFactory())->createRules(new ReflectionClass(ExpressionRules::class)));
        $rules   = new ExpressionRules($parser);
        $scanner = new ExpressionScanner($parser);
        $scanner->scan($expression);
        $scanner->endOfInput();
        static::assertSame($expected, $rules->solution);
    }

    public function testParts(): void
    {
        $parser  = new Parser((new RulesFactory())->createRules(new ReflectionClass(ExpressionRules::class)));
        $rules   = new ExpressionRules($parser);
        $scanner = new ExpressionScanner($parser);
        $scanner->scan("3 +");
        $scanner->scan("5 + 2");
        $scanner->endOfInput();
        static::assertSame(10, $rules->solution);
    }

    public function testReset(): void
    {
        $parser  = new Parser((new RulesFactory())->createRules(new ReflectionClass(ExpressionRules::class)));
        $rules   = new ExpressionRules($parser);
        $scanner = new ExpressionScanner($parser);
        $scanner->scan("3 +");
        $scanner->reset();
        $scanner->scan("5 + 2");
        $scanner->endOfInput();
        static::assertSame(7, $rules->solution);
    }
}
