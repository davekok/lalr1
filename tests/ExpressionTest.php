<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\{Parser,RulesBag,RulesBagFactory};
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \davekok\lalr1\Parser::__construct
 * @covers \davekok\lalr1\Parser::createToken
 * @covers \davekok\lalr1\Parser::endOfTokens
 * @covers \davekok\lalr1\Parser::pushToken
 * @covers \davekok\lalr1\Parser::reduce
 * @covers \davekok\lalr1\Parser::reset
 * @uses \davekok\lalr1\attributes\Rule
 * @uses \davekok\lalr1\attributes\Symbol
 * @uses \davekok\lalr1\attributes\Symbols
 * @uses \davekok\lalr1\Key
 * @uses \davekok\lalr1\Rule
 * @uses \davekok\lalr1\RulesBag
 * @uses \davekok\lalr1\RulesBagFactory
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
        $reader = new ExpressionReader(
            new Parser(
                rulesBag: (new RulesBagFactory)->createRulesBag(new ReflectionClass(ExpressionRules::class)),
                rules:    new ExpressionRules,
            )
        );
        self::assertSame($expected, $reader->read($expression));
    }
}
