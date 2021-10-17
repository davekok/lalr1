<?php

declare(strict_types=1);

namespace davekok\lalr1;

use ReflectionMethod;

class Rules
{
    private readonly array $symbols;
    private readonly array $rules;
    private readonly ReflectionMethod $solutionMethod;

    public function __construct(array $symbols, array $rules, ReflectionMethod $solutionMethod)
    {
        $this->symbols        = $symbols;
        $this->rules          = $rules;
        $this->solutionMethod = $solutionMethod;
    }

    public function getSymbol(string $key): ?Symbol
    {
        return $this->symbols[$key] ?? null;
    }

    public function getRule(string $key): ?Rule
    {
        return $this->rules[$key] ?? null;
    }

    public function solution(object $rulesObject, mixed $value): void
    {
        $this->solutionMethod->invoke($rulesObject, $value);
    }
}
