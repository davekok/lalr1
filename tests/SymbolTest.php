<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use davekok\parser\Key;
use davekok\parser\Rule;
use davekok\parser\Symbol;
use davekok\parser\Symbols;
use davekok\parser\SymbolType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

/**
 * @covers \davekok\parser\Symbol::__construct
 * @covers \davekok\parser\Symbol::__toString
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
