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
            ["startObject", "opening-brace key value"],
            ["addProperty", "object comma key value"],
            ["endObject", "object closing-brace"],
            ["promoteToKey", "string colon"],
        ], $rules);
    }

    public function testEmptyObject(): void
    {
        $parser = new JSONParser();
        $json   = "{}";
        $tokens = iterator_to_array(new JSONScanner($parser->parser, $json));
        static::assertCount(2, $tokens);
        static::assertSame('opening-brace', $tokens[0]->symbol->name);
        static::assertSame('closing-brace', $tokens[1]->symbol->name);

        $rules = iterator_to_array($parser->parser->rules);
        $rule  = $parser->parser->rules->get($tokens[0]->symbol->key . $tokens[1]->symbol->key);
        static::assertEquals($rules[10], $rule);

        $parser->parser->pushToken($tokens[0]);
        $parser->parser->pushToken($tokens[1]);
        $parser->parser->endOfTokens();
        static::assertEquals(new stdClass, $parser->parser->value);
    }
}
