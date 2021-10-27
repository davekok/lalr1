<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\Key;
use davekok\lalr1\Parser;
use davekok\lalr1\Rule;
use davekok\lalr1\RulesFactory;
use davekok\lalr1\Symbol;
use davekok\lalr1\Symbols;
use davekok\stream\ReaderBuffer;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \davekok\lalr1\Parser
 * @covers ::__construct
 * @covers ::createToken
 * @covers ::endOfTokens
 * @covers ::pushToken
 * @covers ::reduce
 * @covers ::setRulesObject
 * @uses \davekok\lalr1\attributes\Rule
 * @uses \davekok\lalr1\attributes\Solution
 * @uses \davekok\lalr1\attributes\Symbol
 * @uses \davekok\lalr1\attributes\Symbols
 * @uses \davekok\lalr1\Key
 * @uses \davekok\lalr1\Rule
 * @uses \davekok\lalr1\Rules
 * @uses \davekok\lalr1\RulesFactory
 * @uses \davekok\lalr1\Symbol
 * @uses \davekok\lalr1\SymbolType
 * @uses \davekok\lalr1\Token
 * @uses \davekok\lalr1\Tokens
 */
class JSONTest extends TestCase
{
    public function simpleData(): array
    {
        return [
            [9384,        "9384"        ],
            [-9384,       "-9384"       ],
            [9384.38437,  "9384.38437"  ],
            [-9384.38437, "-9384.38437" ],
            [4.38437e10,  "4.38437e10"  ],
            [-4.38437e10, "-4.38437e10" ],
            [true,        "true"        ],
            [false,       "false"       ],
            [null,        "null"        ],
            ["sdf\ndf",   "\"sdf\\ndf\""],
        ];
    }

    /**
     * @dataProvider simpleData
     */
    public function testSimple(mixed $expected, string $json): void
    {
        $parser  = new Parser((new RulesFactory())->createRules(new ReflectionClass(JSONRules::class)));
        $rules   = new JSONRules($parser);
        $scanner = new JSONReader($parser);
        $buffer  = new ReaderBuffer($json);
        $scanner->read($buffer);
        $scanner->endOfInput($buffer);
        static::assertSame($expected, $rules->solution);
    }
}
