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

class JSONTest extends TestCase
{
    public function testSymbolAttributes(): void
    {
        $reflection = new ReflectionClass(JSONParser::class);
        [$symbols] = $reflection->getAttributes(Symbols::class);
        $symbols = $symbols->newInstance();
        static::assertInstanceOf(Symbols::class, $symbols);
        foreach ($symbols as $key => $type) {
            static::assertInstanceOf(Symbol::class, $type);
            static::assertSame($key, Key::keyToNumber($type->key));
        }
        static::assertSame("value", $symbols->rootSymbol->name);
    }

    public function testRuleAttributes(): void
    {
        $factory = $this->createMock(ParserFactoryInterface::class);
        $parser = $this->createMock(ParserInterface::class);
        $factory->expects(static::once())->method('createParser')->willReturn($parser);

        $parser = new JSONParser($factory);
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
            ["promoteNull",    "null"                           ],
            ["promoteBoolean", "boolean"                        ],
            ["promoteNumber",  "number"                         ],
            ["promoteString",  "string"                         ],
            ["promoteObject",  "object"                         ],
            ["promoteArray",   "array"                          ],
            ["emptyArray",     "opening-bracket closing-bracket"],
            ["startArray",     "opening-bracket value"          ],
            ["addElement",     "elements comma value"           ],
            ["endArray",       "elements closing-bracket"       ],
            ["emptyObject",    "opening-brace closing-brace"    ],
            ["startObject",    "opening-brace key value"        ],
            ["addProperty",    "properties comma key value"     ],
            ["closeObject",    "properties closing-brace"       ],
            ["promoteToKey",   "string colon"                   ],
        ], $rules);
    }

    public function simpleData(): array
    {
        return [
            [9384,        "9384"        ],
            [-9384,       "-9384"       ],
            [9384.38437,  "9384.38437"  ],
            [-9384.38437, "-9384.38437" ],
            [4.38437e10,  "4.38437e10"  ],
            [-4.38437e10, "-4.38437e10" ],
            [true,        "true"        ],
            [false,       "false"       ],
            [null,        "null"        ],
            ["sdf\ndf",   "\"sdf\\ndf\""],
        ];
    }

    /**
     * @dataProvider simpleData
     */
    public function testSimple(mixed $expected, string $json): void
    {
        static::assertSame($expected, $this->createParser()->parse($json));
    }

    public function testObject(): void
    {
        static::assertEquals(new stdClass, $this->createParser()->parse('{}'));
        static::assertEquals(new stdClass, $this->createParser()->parse('{ }'));
        $o = new stdClass;
        $o->key = "value";
        static::assertEquals($o, $this->createParser()->parse('{"key":"value"}'));
        $o = new stdClass;
        $o->key1 = "value1";
        $o->key2 = "value2";
        static::assertEquals($o, $this->createParser()->parse('{"key1": "value1", "key2": "value2"}'));
    }

    public function testArray(): void
    {
        static::assertSame([], $this->createParser()->parse('[]'));
        static::assertSame([], $this->createParser()->parse('[ ]'));
        static::assertSame(["value"], $this->createParser()->parse('["value"]'));
        static::assertSame(["value", 3748], $this->createParser()->parse('["value", 3748]'));
    }

    private function createParser(): JSONParser
    {
        return new JSONParser(new ParserFactory(), true);
    }
}
