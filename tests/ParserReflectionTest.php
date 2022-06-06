<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\ParserTrait;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use davekok\parser\ParserReflection;
use davekok\parser\ParserReflectionException;
use davekok\parser\Token;
use davekok\parser\attributes\{Rule,Output,Input,InputOutput,Type,Parser};

#[Parser]
#[Output("root")]
#[Type("branch")]
#[Input("leaf1")]
#[Input("leaf2", precedence: 2)]
class MyParserFull
{
    #[Rule("branch", "leaf1 leaf1")]
    public function rule1(mixed $leaf1_1, mixed $leaf1_2): mixed
    {
        return $leaf1_1;
    }

    #[Rule("branch", "leaf1 leaf2")]
    public function rule2(mixed $leaf1, mixed $leaf2): mixed
    {
        return $leaf2;
    }

    #[Rule("root", "branch leaf2", 3)]
    public function rule3(mixed $branch, mixed $leaf2): mixed
    {
        return $branch;
    }
}

#[Parser]
#[InputOutput("inout")]
class MyParserWithInputOutput
{
    #[Rule("inout", "inout")]
    public function rule1(mixed $inout): mixed
    {
        return $inout;
    }
}

#[Parser]
#[Output("output")]
class MyParserNoInput {}

#[Parser]
#[Input("input")]
class MyParserNoOutput {}

class MyParserNoParser {}

class MyParserParserIsNull {
    #[Rule("foo", "foo")]
    public function rule(mixed $foo): mixed
    {
        return $foo;
    }
}

#[Parser]
#[Input("root3")]
#[Output("root")]
#[InputOutput("root2")]
class MyParserDoubleOutput {}

#[Parser]
#[Input("root3")]
#[InputOutput("root2")]
#[Output("root")]
class MyParserDoubleOutputReverse {}

#[Output("output")]
#[Parser]
class MyParserBeforeTypes {}

#[Parser]
#[Output("output")]
#[Input("input")]
#[Input("input")]
class MyParserInputSameName {}

#[Parser]
#[Output("output")]
#[Input("input")]
class MyParserNoRules {}

#[Parser]
#[InputOutput("inout")]
class MyParserNoBinding
{
    #[Rule("inout", "inout")]
    public function rule1(mixed $foo): mixed
    {
        return $foo;
    }
}

#[Parser]
#[InputOutput("inout")]
class MyParserNoSuchType
{
    #[Rule("inout", "foo")]
    public function rule1(mixed $foo): mixed
    {
        return $foo;
    }
}

class ParserReflectionTest extends TestCase
{
    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::autoload
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\ParserReflection::binding
     * @covers \davekok\parser\ParserReflectionRule::__construct
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionFull(): void
    {
        $reflection = new ParserReflection(MyParserFull::class);
        $root = $reflection->types[0];
        static::assertSame(0, $root->id);
        static::assertFalse($root->input);
        static::assertTrue($root->output);
        static::assertSame(addslashes("\0"), addslashes($root->key));
        static::assertSame("root", $root->name);
        static::assertNull($root->pattern);
        static::assertSame(0, $root->precedence);
        $branch = $reflection->types[1];
        static::assertSame(1, $branch->id);
        static::assertFalse($branch->input);
        static::assertFalse($branch->output);
        static::assertSame("\1", $branch->key);
        static::assertSame("branch", $branch->name);
        static::assertNull($branch->pattern);
        static::assertSame(0, $branch->precedence);
        $leaf1 = $reflection->types[2];
        static::assertSame(2, $leaf1->id);
        static::assertTrue($leaf1->input);
        static::assertFalse($leaf1->output);
        static::assertSame("\2", $leaf1->key);
        static::assertSame("leaf1", $leaf1->name);
        static::assertNull($leaf1->pattern);
        static::assertSame(0, $leaf1->precedence);
        $leaf2 = $reflection->types[3];
        static::assertSame(3, $leaf2->id);
        static::assertTrue($leaf2->input);
        static::assertFalse($leaf2->output);
        static::assertSame("\3", $leaf2->key);
        static::assertSame("leaf2", $leaf2->name);
        static::assertNull($leaf2->pattern);
        static::assertSame(2, $leaf2->precedence);
        $rule1 = $reflection->rules[0];
        static::assertSame("rule1", $rule1->name);
        static::assertSame("\2\2", $rule1->key);
        static::assertSame("branch", $rule1->type);
        static::assertSame("leaf1 leaf1", $rule1->rule);
        static::assertSame(0, $rule1->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule1->reducer);
        $rule2 = $reflection->rules[1];
        static::assertSame("rule2", $rule2->name);
        static::assertSame("\2\3", $rule2->key);
        static::assertSame("branch", $rule2->type);
        static::assertSame("leaf1 leaf2", $rule2->rule);
        static::assertSame(2, $rule2->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule2->reducer);
        $rule3 = $reflection->rules[2];
        static::assertSame("rule3", $rule3->name);
        static::assertSame("\1\3", $rule3->key);
        static::assertSame("root", $rule3->type);
        static::assertSame("branch leaf2", $rule3->rule);
        static::assertSame(3, $rule3->precedence);
        static::assertInstanceOf(ReflectionMethod::class, $rule3->reducer);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::autoload
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\ParserReflection::binding
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\ParserReflectionRule::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionWithInputOutput(): void
    {
        $reflection = new ParserReflection(MyParserWithInputOutput::class);
        $inout = $reflection->types[0];
        static::assertSame(0, $inout->id);
        static::assertTrue($inout->input);
        static::assertTrue($inout->output);
        static::assertSame(addslashes("\0"), addslashes($inout->key));
        static::assertSame("inout", $inout->name);
        static::assertNull($inout->pattern);
        static::assertSame(0, $inout->precedence);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionNoInput(): void
    {
        $this->expectExceptionMessage("No Input or InputOutput attribute, expected at least one.");
        new ParserReflection(MyParserNoInput::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionNoOutput(): void
    {
        $this->expectExceptionMessage("No Output or InputOutput attribute, expected one.");
        new ParserReflection(MyParserNoOutput::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     */
    public function testReflectionNoParser(): void
    {
        $this->expectExceptionMessage("No Parser attribute, expected one.");
        new ParserReflection(MyParserNoParser::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\attributes\Rule::__construct
     */
    public function testReflectionParserIsNull(): void
    {
        $this->expectExceptionMessage("No such type 'foo'");
        new ParserReflection(MyParserParserIsNull::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionDoubleOutput(): void
    {
        $this->expectExceptionMessage("Only one Output/InputOutput attribute allowed.");
        new ParserReflection(MyParserDoubleOutput::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionDoubleOutputReverse(): void
    {
        $this->expectExceptionMessage("Only one Output/InputOutput attribute allowed.");
        new ParserReflection(MyParserDoubleOutputReverse::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionParserBeforeTypes(): void
    {
        $this->expectExceptionMessage("Parser attribute must be declared before types.");
        new ParserReflection(MyParserBeforeTypes::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionInputSameName(): void
    {
        $this->expectExceptionMessage("Already have type with name input.");
        new ParserReflection(MyParserInputSameName::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionNoRules(): void
    {
        $this->expectExceptionMessage("No Rule attributes, expected at least one.");
        new ParserReflection(MyParserNoRules::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\ParserReflection::binding
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionNoBinding(): void
    {
        $this->expectExceptionMessage("Unable to detect binding for parameter foo.");
        new ParserReflection(MyParserNoBinding::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\ParserReflection::binding
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionNoSuchType(): void
    {
        $this->expectExceptionMessage("No such type 'foo'.");
        new ParserReflection(MyParserNoSuchType::class);
    }

    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::autoload
     * @covers \davekok\parser\ParserReflection::traitName
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\ParserReflection::binding
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @covers \davekok\parser\ParserReflectionRule::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionAutoloadNoNamespace(): void
    {
        spl_autoload_register(self::autoloadParserNoNamespace(...));
        $reflection = new ParserReflection("MyParserAutoloadNoNamespace");
        spl_autoload_unregister(self::autoloadParserNoNamespace(...));
        self::assertSame("", $reflection->class->getNamespaceName());
        self::assertSame("MyParserAutoloadNoNamespace", $reflection->class->name);
    }

    private static function autoloadParserNoNamespace(string $className): void
    {
        if ($className !== "MyParserAutoloadNoNamespace") {
            return;
        }
        eval(<<<PHP
            use davekok\parser\ParserTrait;
            use davekok\parser\attributes\{Rule,Output,Input,Parser};

            #[Parser]
            #[Output("output")]
            #[Input("input")]
            class MyParserAutoloadNoNamespace {
                use ParserTrait;
                use MyParserAutoloadLexar;
                use MyParserAutoloadStitcher;

                #[Rule("output", "input")]
                private function rule(mixed \$input): mixed {
                    return \$input;
                }
            }
            PHP);
    }
    /**
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::autoload
     * @covers \davekok\parser\ParserReflection::traitName
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\ParserReflection::binding
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @covers \davekok\parser\ParserReflectionRule::__construct
     * @uses \davekok\parser\Key
     */
    public function testReflectionAutoloadWithNamespace(): void
    {
        spl_autoload_register(self::autoloadParserWithNamespace(...));
        $reflection = new ParserReflection("Awesome\Namespace\MyParserAutoloadWithNamespace");
        spl_autoload_unregister(self::autoloadParserWithNamespace(...));
        self::assertSame("Awesome\Namespace", $reflection->class->getNamespaceName());
        self::assertSame("Awesome\Namespace\MyParserAutoloadWithNamespace", $reflection->class->name);
    }

    private static function autoloadParserWithNamespace(string $className): void
    {
        if ($className !== "Awesome\Namespace\MyParserAutoloadWithNamespace") {
            return;
        }
        eval(<<<PHP
            namespace Awesome\Namespace;

            use davekok\parser\ParserTrait;
            use davekok\parser\attributes\{Rule,Output,Input,Parser};

            #[Parser]
            #[Output("output")]
            #[Input("input")]
            class MyParserAutoloadWithNamespace {
                use ParserTrait;
                use MyParserAutoloadLexar;
                use MyParserAutoloadStitcher;

                #[Rule("output", "input")]
                private function rule(mixed \$input): mixed {
                    return \$input;
                }
            }
            PHP);
    }
}
