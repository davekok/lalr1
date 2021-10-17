<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\RulesFactory;
use davekok\lalr1\attributes\{Rule,Solution,Symbol,Symbols};
use davekok\lalr1\SymbolType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

#[Symbols(
    new Symbol(SymbolType::ROOT, "root"),
    new Symbol(SymbolType::BRANCH, "branch"),
    new Symbol(SymbolType::LEAF, "leaf1"),
    new Symbol(SymbolType::LEAF, "leaf2", 2),
)]
class RulesClass
{
    #[Rule("leaf1 leaf1")]
    public function rule1(array $tokens): Token
    {
        return $tokens[0];
    }

    #[Rule("leaf1 leaf2")]
    public function rule2(array $tokens): Token
    {
        return $tokens[0];
    }

    #[Rule("branch leaf2", 3)]
    public function rule3(array $tokens): Token
    {
        return $tokens[0];
    }

    #[Solution]
    public function solution(mixed $value): void
    {
    }
}

/**
 * @covers \davekok\lalr1\RulesFactory::createRules
 * @uses \davekok\lalr1\attributes\Rule
 * @uses \davekok\lalr1\attributes\Symbol
 * @uses \davekok\lalr1\attributes\Symbols
 * @uses \davekok\lalr1\Key
 * @uses \davekok\lalr1\Rule
 * @uses \davekok\lalr1\Rules
 * @uses \davekok\lalr1\Symbol
 * @uses \davekok\lalr1\SymbolType
 */
class RulesFactoryTest extends TestCase
{
    public function testRules(): void
    {
        $factory = new RulesFactory();
        $rules = $factory->createRules(new ReflectionClass(RulesClass::class));
        $root = $rules->getSymbol("root");
        static::assertSame(SymbolType::ROOT, $root->type);
        static::assertSame("\0", $root->key);
        static::assertSame("root", $root->name);
        static::assertSame(0, $root->precedence);
        $branch = $rules->getSymbol("branch");
        static::assertSame(SymbolType::BRANCH, $branch->type);
        static::assertSame("\1", $branch->key);
        static::assertSame("branch", $branch->name);
        static::assertSame(0, $branch->precedence);
        $leaf1 = $rules->getSymbol("leaf1");
        static::assertSame(SymbolType::LEAF, $leaf1->type);
        static::assertSame("\2", $leaf1->key);
        static::assertSame("leaf1", $leaf1->name);
        static::assertSame(0, $leaf1->precedence);
        $leaf2 = $rules->getSymbol("leaf2");
        static::assertSame(SymbolType::LEAF, $leaf2->type);
        static::assertSame("\3", $leaf2->key);
        static::assertSame("leaf2", $leaf2->name);
        static::assertSame(2, $leaf2->precedence);
        $rule1 = $rules->getRule("\2\2");
        static::assertSame("\2\2", $rule1->key);
        static::assertSame("leaf1 leaf1", $rule1->text);
        static::assertSame(0, $rule1->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule1->reduceMethod);
        $rule2 = $rules->getRule("\2\3");
        static::assertSame("\2\3", $rule2->key);
        static::assertSame("leaf1 leaf2", $rule2->text);
        static::assertSame(2, $rule2->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule2->reduceMethod);
        $rule3 = $rules->getRule("\1\3");
        static::assertSame("\1\3", $rule3->key);
        static::assertSame("branch leaf2", $rule3->text);
        static::assertSame(3, $rule3->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule3->reduceMethod);
    }
}
