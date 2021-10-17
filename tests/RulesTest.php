<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\Key;
use davekok\lalr1\Rule;
use davekok\lalr1\Rules;
use davekok\lalr1\RulesFactory;
use davekok\lalr1\Symbol;
use davekok\lalr1\Symbols;
use davekok\lalr1\SymbolType;
use davekok\lalr1\Token;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RulesTest extends TestCase
{
    /**
     * @covers \davekok\lalr1\Rules::__construct
     * @covers \davekok\lalr1\Rules::getSymbol
     * @covers \davekok\lalr1\Rules::getRule
     * @covers \davekok\lalr1\Rules::solution
     * @covers \davekok\lalr1\Rule::__construct
     * @covers \davekok\lalr1\Rule::setKey
     * @covers \davekok\lalr1\Rule::setPrecedence
     * @covers \davekok\lalr1\Rule::setReduceMethod
     * @covers \davekok\lalr1\Rule::reduce
     * @covers \davekok\lalr1\Token::__construct
     * @uses \davekok\lalr1\Symbol
     * @uses \davekok\lalr1\Symbols
     * @uses \davekok\lalr1\Key
     */
    public function testRules(): void
    {
        $value        = new Symbol(SymbolType::ROOT, "value");
        $object       = new Symbol(SymbolType::BRANCH, "object");
        $openingBrace = new Symbol(SymbolType::LEAF, "opening-brace");
        $closingBrace = new Symbol(SymbolType::LEAF, "closing-brace");
        $symbols      = new Symbols($value, $object, $openingBrace, $closingBrace);
        $key1         = $object->key;
        $key2         = $openingBrace->key . $closingBrace->key;
        $rulesObject  = new class() {
            public function rule1(array $tokens): Token { return $tokens[0]; }
            public function rule2(array $tokens): Token { return $tokens[0]; }
            public function solution(mixed $value): void {
                RulesTest::assertSame(1, $value);
            }
        };
        $reflection = new ReflectionClass($rulesObject);
        $expected1  = new Rule("object", 0);
        $expected1->setKey($key1);
        $expected1->setReduceMethod($reflection->getMethod("rule1"));
        $expected2 = new Rule("opening-brace closing-brace");
        $expected2->setKey($key2);
        $expected2->setPrecedence(0);
        $expected2->setReduceMethod($reflection->getMethod("rule2"));
        $rules = new Rules($symbols, [$key1 => $expected1, $key2 => $expected2], $reflection->getMethod("solution"));
        static::assertSame($value, $rules->getSymbol("value"));
        static::assertSame($object, $rules->getSymbol("object"));
        static::assertSame($openingBrace, $rules->getSymbol("opening-brace"));
        static::assertSame($closingBrace, $rules->getSymbol("closing-brace"));
        $token = new Token($value, 1);
        static::assertSame("root:00:value:0:1", "$token");
        static::assertSame($token, $rules->getRule($key1)->reduce($rulesObject, [$token]));
        static::assertSame($token, $rules->getRule($key2)->reduce($rulesObject, [$token]));
        $rules->getSymbol("closing-brace");
        $rules->solution($rulesObject, 1);
    }
}
