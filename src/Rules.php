<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use IteratorAggregate;
use ArrayIterator;
use Traversable;

class Rules implements IteratorAggregate
{
    public function __construct(
        private readonly array $rules
    ) {}

    public function get(string $key): ?RuleStruct
    {
        return $this->rules[$key] ?? null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->rules));
    }
}
