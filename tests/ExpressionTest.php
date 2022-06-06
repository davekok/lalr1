<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\{ParserGenerator,ParserReflection};
use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    /**
     * @covers \davekok\parser\ParserGenerator::__construct
     * @covers \davekok\parser\ParserGenerator::getIterator
     * @covers \davekok\parser\ParserGenerator::cases
     * @covers \davekok\parser\ParserGenerator::findRuleBody
     * @covers \davekok\parser\ParserGenerator::getContexts
     * @covers \davekok\parser\ParserGenerator::matches
     * @covers \davekok\parser\ParserGenerator::reducers
     * @covers \davekok\parser\ParserGenerator::rule
     * @covers \davekok\parser\ParserGenerator::sortByContext
     * @covers \davekok\parser\ParserGenerator::stitcher
     * @covers \davekok\parser\ParserGenerator::token
     * @covers \davekok\parser\ParserGenerator::type
     * @covers \davekok\parser\Key::createKey
     * @covers \davekok\parser\ParserReflection::__construct
     * @covers \davekok\parser\ParserReflection::attributes
     * @covers \davekok\parser\ParserReflection::autoload
     * @covers \davekok\parser\ParserReflection::binding
     * @covers \davekok\parser\ParserReflection::rule
     * @covers \davekok\parser\ParserReflection::traitName
     * @covers \davekok\parser\ParserReflection::type
     * @covers \davekok\parser\ParserReflectionRule::__construct
     * @covers \davekok\parser\ParserReflectionType::__construct
     * @covers \davekok\parser\PhpClass::__construct
     * @covers \davekok\parser\PhpClass::__toString
     * @covers \davekok\parser\PhpClass::comment
     * @covers \davekok\parser\PhpClass::constructor
     * @covers \davekok\parser\PhpClass::end
     * @covers \davekok\parser\PhpClass::implements
     * @covers \davekok\parser\PhpClass::public
     * @covers \davekok\parser\PhpClassVisibility::__construct
     * @covers \davekok\parser\PhpClassVisibility::constructor
     * @covers \davekok\parser\PhpClassVisibility::method
     * @covers \davekok\parser\PhpClassVisibility::property
     * @covers \davekok\parser\PhpCodeStyle::__construct
     * @covers \davekok\parser\PhpConstructor::__construct
     * @covers \davekok\parser\PhpConstructor::param
     * @covers \davekok\parser\PhpConstructor::public
     * @covers \davekok\parser\PhpConstructor::readonly
     * @covers \davekok\parser\PhpConstructorParameter::__construct
     * @covers \davekok\parser\PhpConstructorParameter::public
     * @covers \davekok\parser\PhpConstructorParameter::__toString
     * @covers \davekok\parser\PhpConstructorReadonly::__construct
     * @covers \davekok\parser\PhpConstructorReadonly::param
     * @covers \davekok\parser\PhpConstructorVisibility::__construct
     * @covers \davekok\parser\PhpConstructorVisibility::readonly
     * @covers \davekok\parser\PhpEnum::__construct
     * @covers \davekok\parser\PhpEnum::__toString
     * @covers \davekok\parser\PhpEnum::case
     * @covers \davekok\parser\PhpEnum::cases
     * @covers \davekok\parser\PhpEnum::comment
     * @covers \davekok\parser\PhpEnum::end
     * @covers \davekok\parser\PhpEnum::method
     * @covers \davekok\parser\PhpEnum::public
     * @covers \davekok\parser\PhpEnum::stringBacked
     * @covers \davekok\parser\PhpEnum::implements
     * @covers \davekok\parser\PhpEnumVisibility::__construct
     * @covers \davekok\parser\PhpEnumVisibility::method
     * @covers \davekok\parser\PhpFile::__construct
     * @covers \davekok\parser\PhpFile::__toString
     * @covers \davekok\parser\PhpFile::class
     * @covers \davekok\parser\PhpFile::enum
     * @covers \davekok\parser\PhpFile::namespace
     * @covers \davekok\parser\PhpFile::reference
     * @covers \davekok\parser\PhpFile::trait
     * @covers \davekok\parser\PhpMethod::__construct
     * @covers \davekok\parser\PhpMethod::__toString
     * @covers \davekok\parser\PhpMethod::body
     * @covers \davekok\parser\PhpMethod::param
     * @covers \davekok\parser\PhpMethod::returns
     * @covers \davekok\parser\PhpParameter::__construct
     * @covers \davekok\parser\PhpParameter::__toString
     * @covers \davekok\parser\PhpParameter::body
     * @covers \davekok\parser\PhpParameter::default
     * @covers \davekok\parser\PhpParameter::param
     * @covers \davekok\parser\PhpParameter::type
     * @covers \davekok\parser\PhpProperty::__construct
     * @covers \davekok\parser\PhpProperty::__toString
     * @covers \davekok\parser\PhpProperty::default
     * @covers \davekok\parser\PhpProperty::private
     * @covers \davekok\parser\PhpProperty::type
     * @covers \davekok\parser\PhpText::__construct
     * @covers \davekok\parser\PhpText::blockComment
     * @covers \davekok\parser\PhpText::bodyClose
     * @covers \davekok\parser\PhpText::bodyOpen
     * @covers \davekok\parser\PhpText::emptyBody
     * @covers \davekok\parser\PhpText::inddec
     * @covers \davekok\parser\PhpText::indent
     * @covers \davekok\parser\PhpText::indinc
     * @covers \davekok\parser\PhpText::line
     * @covers \davekok\parser\PhpText::parts
     * @covers \davekok\parser\PhpText::value
     * @covers \davekok\parser\PhpTrait::__construct
     * @covers \davekok\parser\PhpTrait::__toString
     * @covers \davekok\parser\PhpTrait::comment
     * @covers \davekok\parser\PhpTrait::method
     * @covers \davekok\parser\PhpTrait::private
     * @covers \davekok\parser\PhpTrait::property
     * @covers \davekok\parser\PhpTrait::public
     * @covers \davekok\parser\attributes\Parser::__construct
     * @covers \davekok\parser\attributes\Rule::__construct
     * @covers \davekok\parser\attributes\Type::__construct
     */
    public function testGenerator(): void
    {
//        foreach (["Rule", "Stitcher", "Token", "Type"] as $postfix) {
//            $file = __DIR__ . "/ExpressionParser$postfix.php";
//            if (file_exists($file)) {
//                unlink($file);
//            }
//        }

        foreach (new ParserGenerator(new ParserReflection(ExpressionParser::class)) as $phpFile) {
            file_put_contents($phpFile->name, (string)$phpFile);
        }

        foreach (["Rule", "Stitcher", "Token", "Type"] as $postfix) {
            $file = __DIR__ . "/ExpressionParser$postfix.php";
            self::assertFileExists($file);
        }
    }

    public function simpleData(): array
    {
        return [
            [9384,        "9384"               ],
            [9384.38437,  "9384.38437         "],
            [13,          "8 + 5              "],
            [3,           "8 - 5              "],
            [40,          "8 * 5              "],
            [1.6,         "8 / 5              "],
            [3,           "8 \\ 5             "],
            [8,           "(8)                "],
            [43,          "8 * 5 + 3          "],
            [43,          "3 + 8 * 5          "],
            [43,          "3 + (8 * 5)        "],
            [55,          "(3 + 8) * 5        "],
            [41,          "3 + 8 * 5 - 2      "],
            [42,          "3 + 8 * 5 - 2 / 2  "],
            [20.5,        "(3 + 8 * 5 - 2) / 2"],
        ];
    }

    /**
     * @dataProvider simpleData
     * @covers \davekok\parser\ParserTrait::parseTokens
     * @covers \davekok\parser\ParserTrait::pushToken
     * @covers \davekok\parser\ParserTrait::scanTokens
     * @uses \davekok\parser\Tokens
     */
    public function testSimple(mixed $expected, string $expression): void
    {
        $parser = new ExpressionParser();
        foreach ($parser->parse($expression) as $value) {
            self::assertSame($expected, $value);
        }
    }
}
