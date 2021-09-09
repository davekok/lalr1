<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Symbols;
use DaveKok\LALR1\Symbol;
use DaveKok\LALR1\Rule;
use DaveKok\LALR1\Key;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

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
        $parser = new JSONParser();
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
            ["promoteNull", "null"],
            ["promoteBoolean", "boolean"],
            ["promoteNumber", "number"],
            ["promoteString", "string"],
            ["promoteObject", "object"],
            ["promoteArray", "array"],
            ["emptyArray", "opening-bracket closing-bracket"],
            ["startArray", "opening-bracket value"],
            ["addElement", "array comma value"],
            ["endArray", "array closing-brace"],
            ["emptyObject", "opening-brace closing-brace"],
            ["fullObject", "opening-brace properties closing-brace"],
            ["startProperties", "key value"],
            ["addProperty", "properties comma key value"],
            ["promoteToKey", "string colon"],
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
            [[],          "[]"          ],
            [[],          "[ ]"         ],
        ];
    }

    /**
     * @dataProvider simpleData
     */
    public function testSimple(mixed $expected, string $json): void
    {
        static::assertSame($expected, (new JSONParser())->parse($json));
    }

    public function testObject(): void
    {
        // static::assertEquals(new stdClass, (new JSONParser())->parse('{}'));
        // static::assertEquals(new stdClass, (new JSONParser())->parse('{ }'));
        $o = new stdClass;
        $o->key = "value";
        static::assertEquals($o, (new JSONParser())->parse('{"key":"value"}'));
    }
}
