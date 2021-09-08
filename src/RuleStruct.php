<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class RuleStruct
{
    public function __construct(
        public readonly string $key,
        public readonly int $precedence,
        public readonly mixed $reduce
    ) {}
}
