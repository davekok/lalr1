<?php

declare(strict_types=1);

namespace davekok\lalr1;

use ReflectionMethod;

class Rule
{
    public readonly string $key;
    public readonly string $text;
    public readonly int $precedence;
    public readonly ReflectionMethod $reduceMethod;

    public function __construct(string $key, string $text, int $precedence, ReflectionMethod $reduceMethod)
    {
        $this->key = $key;
        $this->text = $text;
        $this->precedence = $precedence;
        $this->reduceMethod = $reduceMethod;
    }

    public function reduce(object $rulesObject, array $tokens): Token
    {
        return $this->reduceMethod->invoke($rulesObject, $tokens);
    }

    public function __toString(): string
    {
        return "[{$this->precedence}] {$this->text}";
    }
}
