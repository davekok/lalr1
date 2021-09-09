<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\BranchSymbol;
use DaveKok\LALR1\Key;
use DaveKok\LALR1\LeafSymbol;
use DaveKok\LALR1\RootSymbol;
use DaveKok\LALR1\Rule;
use DaveKok\LALR1\RulesFactory;
use DaveKok\LALR1\RuleStruct;
use DaveKok\LALR1\Symbol;
use DaveKok\LALR1\Symbols;
use DaveKok\LALR1\Token;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RulesFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $value        = new RootSymbol("value");
        $object       = new BranchSymbol("object");
        $openingBrace = new LeafSymbol("opening-brace");
        $closingBrace = new LeafSymbol("closing-brace");
        $symbols      = new Symbols($value, $object, $openingBrace, $closingBrace);
        $key1         = $object->key;
        $key2         = $openingBrace->key . $closingBrace->key;
        $callable1    = fn(Token $o) => $o;
        $callable2    = fn(Token $o, Token $c) => $o;
        $expected1    = new RuleStruct($key1, 0, $callable1);
        $expected2    = new RuleStruct($key2, 0, $callable2);

        $factory      = new RulesFactory($symbols);
        $factory->addRule(new Rule("object"), $callable1);
        $factory->addRule(new Rule("opening-brace closing-brace"), $callable2);

        $rules = $factory->createRules();
        $array = iterator_to_array($rules);
        static::assertCount(2, $array);
        [$actual1, $actual2] = $array;
        static::assertEquals($expected1, $actual1, "iterate rule 1");
        static::assertEquals($expected2, $actual2, "iterate rule 2");

        static::assertEquals($expected1, $rules->get($key1), "get rule 1");
        static::assertEquals($expected2, $rules->get($key2), "get rule 2");
    }
}
