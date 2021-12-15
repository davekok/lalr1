<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\Parser;
use davekok\parser\Rule;
use davekok\parser\Rules;
use davekok\parser\RulesBag;
use davekok\parser\RulesBagFactory;
use davekok\parser\Symbol;
use davekok\parser\SymbolType;
use davekok\parser\Token;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RulesBagTest extends TestCase
{
    /**
     * @covers \davekok\parser\Rule::__construct
     * @covers \davekok\parser\Rule::__toString
     * @covers \davekok\parser\Rule::reduce
     * @covers \davekok\parser\RulesBag::__construct
     * @covers \davekok\parser\RulesBag::getRule
     * @covers \davekok\parser\RulesBag::getSymbol
     * @uses \davekok\parser\Symbol
     * @uses \davekok\parser\Token
     */
    public function testRulesBag(): void
    {
        $value        = new Symbol(SymbolType::ROOT, "\0", "value", 0);
        $object       = new Symbol(SymbolType::BRANCH, "\1", "object", 0);
        $openingBrace = new Symbol(SymbolType::LEAF, "\2", "opening-brace", 0);
        $closingBrace = new Symbol(SymbolType::LEAF, "\3", "closing-brace", 0);
        $symbols      = [
            "value"         => $value,
            "object"        => $object,
            "opening-brace" => $openingBrace,
            "closing-brace" => $closingBrace,
        ];
        $key1  = $object->key;
        $key2  = $openingBrace->key . $closingBrace->key;
        $rules = new class() implements Rules {
            public function setParser(Parser $parser): void { }
            public function rule1(array $tokens): Token { return $tokens[0]; }
            public function rule2(array $tokens): Token { return $tokens[0]; }
        };
        $reflection = new ReflectionClass($rules);
        $expected1  = new Rule($key1, "object", 0, $reflection->getMethod("rule1"));
        static::assertSame('[0] object', (string)$expected1);
        $expected2 = new Rule($key2, "opening-brace closing-brace", 0, $reflection->getMethod("rule2"));
        static::assertSame('[0] opening-brace closing-brace', (string)$expected2);
        $rulesBag = new RulesBag($symbols, [$key1 => $expected1, $key2 => $expected2]);
        static::assertSame($value, $rulesBag->getSymbol("value"));
        static::assertSame($object, $rulesBag->getSymbol("object"));
        static::assertSame($openingBrace, $rulesBag->getSymbol("opening-brace"));
        static::assertSame($closingBrace, $rulesBag->getSymbol("closing-brace"));
        $token = new Token($value, 1);
        static::assertSame('{"symbol":{"type":"root","key":"\u0000","name":"value","precedence":0},"value":1}', "$token");
        static::assertSame($token, $rulesBag->getRule($key1)->reduce($rules, [$token]));
        static::assertSame($token, $rulesBag->getRule($key2)->reduce($rules, [$token]));
        $rulesBag->getSymbol("closing-brace");
    }
}
