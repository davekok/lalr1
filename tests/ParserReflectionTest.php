<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use davekok\parser\ParserReflection;
use davekok\parser\ParserReflectionException;
use davekok\parser\Token;
use davekok\parser\attributes\{Rule,Output,Input,InputOutput,Type,Parser};

#[Parser]
#[Output("root", "root")]
#[Type("branch", "branch")]
#[Input("leaf1", "leaf1")]
#[Input("leaf2", "leaf2", 2)]
class MyParser
{
    #[Rule("branch", "leaf1 leaf1")]
    public function rule1(mixed $leaf1, mixed $leaf2): mixed
    {
        return $leaf1;
    }

    #[Rule("branch", "leaf1 leaf2")]
    public function rule2(mixed $leaf1, mixed $leaf2): mixed
    {
        return $leaf2;
    }

    #[Rule("root", "branch leaf2", 3)]
    public function rule3(mixed $branch1, mixed $leaf2): mixed
    {
        return $branch1;
    }
}

#[Parser]
#[InputOutput("root", "root")]
class MyParser2 {}

#[Parser]
#[Output("root", "root")]
class MyParser3 {}

#[Parser]
#[Input("root", "root")]
class MyParser4 {}

class MyParser5 {}

#[Parser]
#[Input("root3", "root3")]
#[Output("root", "root")]
#[InputOutput("root2", "root2")]
class MyParser6 {}

#[Parser]
#[Input("root3", "root3")]
#[InputOutput("root2", "root2")]
#[Output("root", "root")]
class MyParser7 {}

class ParserReflectionTest extends TestCase
{
    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::parser
     * @covers \davekok\parser\ParserReflection::types
     * @covers \davekok\parser\ParserReflection::rules
     * @covers \davekok\parser\ParserReflectionRule::__construct
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflection(): void
    {
        $reflection = new ParserReflection(MyParser::class);
        $root = $reflection->types["root"];
        static::assertSame(0, $root->id);
        static::assertFalse($root->input);
        static::assertTrue($root->output);
        static::assertSame(addslashes("\0"), addslashes($root->key));
        static::assertSame("root", $root->name);
        static::assertSame("root", $root->text);
        static::assertSame(0, $root->precedence);
        $branch = $reflection->types["branch"];
        static::assertSame(1, $branch->id);
        static::assertFalse($branch->input);
        static::assertFalse($branch->output);
        static::assertSame("\1", $branch->key);
        static::assertSame("branch", $branch->name);
        static::assertSame("branch", $branch->text);
        static::assertSame(0, $branch->precedence);
        $leaf1 = $reflection->types["leaf1"];
        static::assertSame(2, $leaf1->id);
        static::assertTrue($leaf1->input);
        static::assertFalse($leaf1->output);
        static::assertSame("\2", $leaf1->key);
        static::assertSame("leaf1", $leaf1->name);
        static::assertSame("leaf1", $leaf1->text);
        static::assertSame(0, $leaf1->precedence);
        $leaf2 = $reflection->types["leaf2"];
        static::assertSame(3, $leaf2->id);
        static::assertTrue($leaf2->input);
        static::assertFalse($leaf2->output);
        static::assertSame("\3", $leaf2->key);
        static::assertSame("leaf2", $leaf2->name);
        static::assertSame("leaf2", $leaf2->text);
        static::assertSame(2, $leaf2->precedence);
        $rule1 = $reflection->rules[0];
        static::assertSame("rule1", $rule1->name);
        static::assertSame("\2\2", $rule1->key);
        static::assertSame("branch", $rule1->type);
        static::assertSame("leaf1 leaf1", $rule1->text);
        static::assertSame(0, $rule1->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule1->reducer);
        $rule2 = $reflection->rules[1];
        static::assertSame("rule2", $rule2->name);
        static::assertSame("\2\3", $rule2->key);
        static::assertSame("branch", $rule2->type);
        static::assertSame("leaf1 leaf2", $rule2->text);
        static::assertSame(2, $rule2->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule2->reducer);
        $rule3 = $reflection->rules[2];
        static::assertSame("rule3", $rule3->name);
        static::assertSame("\1\3", $rule3->key);
        static::assertSame("root", $rule3->type);
        static::assertSame("branch leaf2", $rule3->text);
        static::assertSame(3, $rule3->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule3->reducer);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::parser
     * @covers \davekok\parser\ParserReflection::types
     * @covers \davekok\parser\ParserReflection::rules
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionWithInputOutput(): void
    {
        $reflection = new ParserReflection(MyParser2::class);
        $root = $reflection->types["root"];
        static::assertSame(0, $root->id);
        static::assertTrue($root->input);
        static::assertTrue($root->output);
        static::assertSame(addslashes("\0"), addslashes($root->key));
        static::assertSame("root", $root->name);
        static::assertSame("root", $root->text);
        static::assertSame(0, $root->precedence);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::parser
     * @covers \davekok\parser\ParserReflection::types
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionNoInput(): void
    {
        $this->expectException(ParserReflectionException::class);
        new ParserReflection(MyParser3::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::parser
     * @covers \davekok\parser\ParserReflection::types
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionNoOutput(): void
    {
        $this->expectException(ParserReflectionException::class);
        new ParserReflection(MyParser4::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::parser
     */
    public function testReflectionNoParser(): void
    {
        $this->expectException(ParserReflectionException::class);
        new ParserReflection(MyParser5::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::parser
     * @covers \davekok\parser\ParserReflection::types
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionDoubleOutput(): void
    {
        $this->expectException(ParserReflectionException::class);
        new ParserReflection(MyParser6::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::parser
     * @covers \davekok\parser\ParserReflection::types
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionDoubleOutputReverse(): void
    {
        $this->expectException(ParserReflectionException::class);
        new ParserReflection(MyParser7::class);
    }
}
