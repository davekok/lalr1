<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use Attribute;
use IteratorAggregate;
use ArrayIterator;
use Traversable;
use Exception;

#[Attribute]
class Symbols implements IteratorAggregate
{
    private readonly array      $symbolsMappedByKey;
    private readonly array      $symbolsMappedByName;
    public  readonly RootSymbol $rootSymbol;

    public function __construct(Symbol ...$symbols)
    {
        $symbolsMappedByKey  = [];
        $symbolsMappedByName = [];
        foreach ($symbols as $key => $symbol) {
            $symbol->setKey($key);
            $symbolsMappedByKey[$symbol->key]   = $symbol;
            $symbolsMappedByName[$symbol->name] = $symbol;
            if ($symbol instanceof RootSymbol) {
                if (isset($this->rootSymbol)) {
                    throw new Exception("There can be only one root symbol.");
                }
                $this->rootSymbol = $symbol;
            }
        }
        $this->symbolsMappedByKey  = $symbolsMappedByKey;
        $this->symbolsMappedByName = $symbolsMappedByName;
    }

    public function getByKey(string $key): Symbol
    {
        return $this->symbolsMappedByKey[$key]
            ?? throw new Exception("Symbol not found " . Key::keyToNumber($key));
    }

    public function getByName(string $name): Symbol
    {
        return $this->symbolsMappedByName[$name] ?? throw new Exception("No such symbol $name.");
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->symbolsMappedByKey));
    }
}
