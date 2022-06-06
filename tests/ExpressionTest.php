<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\{ParserGenerator,ParserReflection};
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;

class ExpressionTest extends TestCase
{
    /**
     * @covers \davekok\parser\ParserGenerator::__construct
     * @covers \davekok\parser\ParserGenerator::getIterator
     * @covers \davekok\parser\ParserGenerator::indinc
     * @covers \davekok\parser\ParserGenerator::inddec
     * @covers \davekok\parser\ParserGenerator::indent
     * @covers \davekok\parser\ParserGenerator::safestr
     * @uses \davekok\parser\ParserReflection
     * @uses \davekok\parser\Rule
     * @uses \davekok\parser\Symbol
     * @uses \davekok\parser\attributes\Rule
     * @uses \davekok\parser\attributes\Symbol
     * @uses \davekok\parser\attributes\Symbols
     */
    public function testGenerator(): void
    {
        unlink(__DIR__ . "/ExpressionParserRule.php");
        unlink(__DIR__ . "/ExpressionParserStitcher.php");
        unlink(__DIR__ . "/ExpressionParserToken.php");
        unlink(__DIR__ . "/ExpressionParserType.php");

        foreach (new ParserGenerator(new ParserReflection(ExpressionParser::class)) as $phpFile) {
            file_put_contents($phpFile->name, (string)$phpFile);
        }

        self::assertFileExists(__DIR__ . "/ExpressionParserRule.php");
        self::assertFileExists(__DIR__ . "/ExpressionParserStitcher.php");
        self::assertFileExists(__DIR__ . "/ExpressionParserToken.php");
        self::assertFileExists(__DIR__ . "/ExpressionParserType.php");
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
