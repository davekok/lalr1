<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Symbols;
use DaveKok\LALR1\Symbol;
use DaveKok\LALR1\Rule;
use DaveKok\LALR1\Key;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class SymbolsTest extends TestCase
{
    public function testSymbols(): void
    {
        $root = new RootSymbol("root");
        $branch = new BranchSymbol("branch");
        $leaf = new BranchSymbol("leaf");
        $symbols = new Symbols($root, $branch, $leaf);
        $array = iterator_to_array($symbols);
        static::assertCount(3, $array);
        static::assertSame($root, $array[0]);
        static::assertSame($branch, $array[1]);
        static::assertSame($leaf, $array[2]);
        static::assertSame($root, $symbols->getByName("root"));
        static::assertSame($branch, $symbols->getByName("branch"));
        static::assertSame($leaf, $symbols->getByName("leaf"));
        static::assertSame($root, $symbols->getByKey(Key::numberToKey(0)));
        static::assertSame($branch, $symbols->getByKey(Key::numberToKey(1)));
        static::assertSame($leaf, $symbols->getByKey(Key::numberToKey(2)));
    }
}
