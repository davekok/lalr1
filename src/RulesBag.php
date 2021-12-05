<?php

declare(strict_types=1);

namespace davekok\lalr1;

use ReflectionMethod;

class RulesBag
{
    public function __construct(
        private readonly array $symbols,
        private readonly array $rules,
    ) {}

    public function getSymbol(string $key): ?Symbol
    {
        return $this->symbols[$key] ?? null;
    }

    public function getRule(string $key): ?Rule
    {
        return $this->rules[$key] ?? null;
    }
}
