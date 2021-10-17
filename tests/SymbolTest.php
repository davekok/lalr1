<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\Key;
use davekok\lalr1\Rule;
use davekok\lalr1\Symbol;
use davekok\lalr1\Symbols;
use davekok\lalr1\SymbolType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

/**
 * @covers \davekok\lalr1\Symbol::__construct
 * @covers \davekok\lalr1\Symbol::__toString
 * @covers \davekok\lalr1\SymbolType::label
 */
class SymbolTest extends TestCase
{
    public function testSymbols(): void
    {
        $root = new Symbol(SymbolType::ROOT, "\0", "root", 0);
        static::assertSame('{"type":"root","key":"\u0000","name":"root","precedence":0}', (string)$root);
        $branch = new Symbol(SymbolType::BRANCH, "\1", "branch", 0);
        static::assertSame('{"type":"branch","key":"\u0001","name":"branch","precedence":0}', (string)$branch);
        $leaf = new Symbol(SymbolType::LEAF, "\2", "leaf", 0);
        static::assertSame('{"type":"leaf","key":"\u0002","name":"leaf","precedence":0}', (string)$leaf);
    }
}
