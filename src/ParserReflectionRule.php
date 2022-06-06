<?php

declare(strict_types=1);

namespace davekok\parser;

use ReflectionMethod;

class ParserReflectionRule
{
    public function __construct(
        public readonly string $name,
        public readonly string $key,
        public readonly string $type,
        public readonly string $rule,
        public readonly int $precedence,
        public readonly ReflectionMethod $reducer,
        public readonly array $binding,
        public readonly array $context,
    ) {}
}
