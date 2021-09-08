<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Symbols;
use DaveKok\LALR1\Symbol;
use DaveKok\LALR1\RootSymbol;
use DaveKok\LALR1\BranchSymbol;
use DaveKok\LALR1\LeafSymbol;
use DaveKok\LALR1\Token;
use DaveKok\LALR1\Rule;
use DaveKok\LALR1\Key;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RulesFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $symbols = new Symbols(
            new RootSymbol("value"),
            new BranchSymbol("object"),
            new LeafSymbol("opening-brace"),
            new LeafSymbol("closing-brace")
        );
        $factory = new RulesFactory($symbols);
        $factory->addRule(new Rule("object"), fn(Token $o) => $o);
        $factory->addRule(new Rule("opening-brace closing-brace"), fn(Token $o, Token $c) => $o);
    }
}
