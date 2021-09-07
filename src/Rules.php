<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use Attribute;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

#[Attribute]
class Rules
{
    private readyonly array $rules;

    public function __construct(Rule ...$rules)
    {
        $mappedRules = [];
        foreach ($rules as $rule) {
            $mappedRules[$rule->key] = $rule;
        }
        $this->rules = $mappedRules;
    }

    public function get(string $key): ?Rule
    {
        return $this->rules[$key] ?? null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->types));
    }
}
