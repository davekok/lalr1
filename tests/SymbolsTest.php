<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Symbols;
use DaveKok\LALR1\RootSymbol;
use DaveKok\LALR1\BranchSymbol;
use DaveKok\LALR1\LeafSymbol;
use DaveKok\LALR1\Rule;
use DaveKok\LALR1\Key;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class SymbolsTest extends TestCase
{
    public function testSymbols(): void
    {
        $root    = new RootSymbol("root");
        $branch  = new BranchSymbol("branch");
        $leaf    = new LeafSymbol("leaf");
        $symbols = new Symbols($root, $branch, $leaf);
        $array   = iterator_to_array($symbols);
        static::assertCount(3, $array);
        static::assertSame($root, $array[0], "iterate 0");
        static::assertSame($branch, $array[1], "iterate 1");
        static::assertSame($leaf, $array[2], "iterate 2");
        static::assertSame($root, $symbols->getByName("root"), "by name root");
        static::assertSame($branch, $symbols->getByName("branch"), "by name branch");
        static::assertSame($leaf, $symbols->getByName("leaf"), "by name leaf");
        static::assertSame($root, $symbols->getByKey(Key::numberToKey(0)), "by key 0");
        static::assertSame($branch, $symbols->getByKey(Key::numberToKey(1)), "by key 1");
        static::assertSame($leaf, $symbols->getByKey(Key::numberToKey(2)), "by key 2");
    }
}
