<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\{Parser,RulesBag,RulesBagFactory};
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \davekok\parser\Parser::__construct
 * @covers \davekok\parser\Parser::createToken
 * @covers \davekok\parser\Parser::endOfTokens
 * @covers \davekok\parser\Parser::pushToken
 * @covers \davekok\parser\Parser::reduce
 * @covers \davekok\parser\Parser::reset
 * @uses \davekok\parser\attributes\Rule
 * @uses \davekok\parser\attributes\Symbol
 * @uses \davekok\parser\attributes\Symbols
 * @uses \davekok\parser\Key
 * @uses \davekok\parser\Rule
 * @uses \davekok\parser\RulesBag
 * @uses \davekok\parser\RulesBagFactory
 * @uses \davekok\parser\Symbol
 * @uses \davekok\parser\Token
 * @uses \davekok\parser\Tokens
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
