<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\Parser;
use davekok\parser\Rules;
use davekok\parser\RulesBagFactory;
use davekok\parser\attributes\{Rule,Symbol,Symbols};
use davekok\parser\SymbolType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

#[Symbols(
    new Symbol(SymbolType::ROOT, "root"),
    new Symbol(SymbolType::BRANCH, "branch"),
    new Symbol(SymbolType::LEAF, "leaf1"),
    new Symbol(SymbolType::LEAF, "leaf2", 2),
)]
class RulesClass implements Rules
{
    public function setParser(Parser $parser): void {}

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
}

/**
 * @covers \davekok\parser\RulesBagFactory::createRulesBag
 * @uses \davekok\parser\attributes\Rule
 * @uses \davekok\parser\attributes\Symbol
 * @uses \davekok\parser\attributes\Symbols
 * @uses \davekok\parser\Key
 * @uses \davekok\parser\Rule
 * @uses \davekok\parser\RulesBag
 * @uses \davekok\parser\Symbol
 * @uses \davekok\parser\SymbolType
 */
class RulesBagFactoryTest extends TestCase
{
    public function testRulesBag(): void
    {
        $factory = new RulesBagFactory();
        $rulesBag = $factory->createRulesBag(new ReflectionClass(RulesClass::class));
        $root = $rulesBag->getSymbol("root");
        static::assertSame(SymbolType::ROOT, $root->type);
        static::assertSame("\0", $root->key);
        static::assertSame("root", $root->name);
        static::assertSame(0, $root->precedence);
        $branch = $rulesBag->getSymbol("branch");
        static::assertSame(SymbolType::BRANCH, $branch->type);
        static::assertSame("\1", $branch->key);
        static::assertSame("branch", $branch->name);
        static::assertSame(0, $branch->precedence);
        $leaf1 = $rulesBag->getSymbol("leaf1");
        static::assertSame(SymbolType::LEAF, $leaf1->type);
        static::assertSame("\2", $leaf1->key);
        static::assertSame("leaf1", $leaf1->name);
        static::assertSame(0, $leaf1->precedence);
        $leaf2 = $rulesBag->getSymbol("leaf2");
        static::assertSame(SymbolType::LEAF, $leaf2->type);
        static::assertSame("\3", $leaf2->key);
        static::assertSame("leaf2", $leaf2->name);
        static::assertSame(2, $leaf2->precedence);
        $rule1 = $rulesBag->getRule("\2\2");
        static::assertSame("\2\2", $rule1->key);
        static::assertSame("leaf1 leaf1", $rule1->text);
        static::assertSame(0, $rule1->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule1->reduceMethod);
        $rule2 = $rulesBag->getRule("\2\3");
        static::assertSame("\2\3", $rule2->key);
        static::assertSame("leaf1 leaf2", $rule2->text);
        static::assertSame(2, $rule2->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule2->reduceMethod);
        $rule3 = $rulesBag->getRule("\1\3");
        static::assertSame("\1\3", $rule3->key);
        static::assertSame("branch leaf2", $rule3->text);
        static::assertSame(3, $rule3->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule3->reduceMethod);
    }
}
