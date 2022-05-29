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
        public readonly string $text,
        public readonly int $precedence,
        public readonly ReflectionMethod $reducer,
    ) {}
}
