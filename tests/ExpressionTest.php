<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Key;
use DaveKok\LALR1\Parser;
use DaveKok\LALR1\ParserInterface;
use DaveKok\LALR1\ParserFactory;
use DaveKok\LALR1\ParserFactoryInterface;
use DaveKok\LALR1\Rule;
use DaveKok\LALR1\Symbol;
use DaveKok\LALR1\Symbols;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Exception;

class ExpressionTest extends TestCase
{
    public function testSymbolAttributes(): void
    {
        $reflection = new ReflectionClass(ExpressionParser::class);
        [$symbols] = $reflection->getAttributes(Symbols::class);
        $symbols = $symbols->newInstance();
        static::assertInstanceOf(Symbols::class, $symbols);
        foreach ($symbols as $key => $type) {
            static::assertInstanceOf(Symbol::class, $type);
            static::assertSame($key, Key::keyToNumber($type->key));
        }
        static::assertSame("number", $symbols->rootSymbol->name);
    }

    public function testRuleAttributes(): void
    {
        $factory = $this->createMock(ParserFactoryInterface::class);
        $parser = $this->createMock(ParserInterface::class);
        $factory->expects(static::once())->method('createParser')->willReturn($parser);

        $parser = new ExpressionParser($factory);
        $reflection = new ReflectionClass($parser);
        $methods = $reflection->getMethods();
        $rules = [];
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Rule::class);
            if (count($attributes) === 1) {
                [$attribute] = $attributes;
                $rules[] = [$method->name, $attribute->newInstance()->text];
            }
        }
        static::assertSame([
            ["add",       "number + number" ],
            ["substract", "number - number" ],
            ["negate",    "- number"        ],
            ["multiply",  "number * number" ],
            ["divide",    "number / number" ],
            ["modulus",   "number \\ number"],
            ["group",     "( number )"      ],
        ], $rules);
    }

    public function simpleData(): array
    {
        return [
            [9384,        "9384"        ],
            [9384.38437,  "9384.38437"  ],
            [4.38437e10,  "4.38437e10"  ],
        ];
    }

    /**
     * @dataProvider simpleData
     */
    public function testSimple(mixed $expected, string $expression): void
    {
        static::assertSame($expected, $this->createParser()->parse($expression));
    }

    public function simpleExpressions(): array
    {
        return [
            [13,  "8 + 5"  ],
            [3,   "8 - 5"  ],
            [40,  "8 * 5"  ],
            [1.6, "8 / 5"  ],
            [3,   "8 \\ 5" ],
            [8,   "(8)"    ],
        ];
    }

    /**
     * @dataProvider simpleExpressions
     */
    public function testSimpleExpressions(mixed $expected, string $expression): void
    {
        static::assertSame($expected, $this->createParser()->parse($expression));
    }

    public function advancedExpressions(): array
    {
        return [
            [43,  "8 * 5 + 3"  ],
            [43,  "3 + 8 * 5"  ],
            [43,  "3 + (8 * 5)"  ],
            [55,  "(3 + 8) * 5"  ],
        ];
    }

    /**
     * @dataProvider advancedExpressions
     */
    public function testAdvancedExpressions(mixed $expected, string $expression): void
    {
        static::assertSame($expected, $this->createParser()->parse($expression));
    }

    private function createParser(): ExpressionParser
    {
        return new ExpressionParser(new ParserFactory(), true);
    }
}
