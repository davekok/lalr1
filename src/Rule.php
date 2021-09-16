<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use Attribute;

#[Attribute]
class Rule
{
    public function __construct(
        public readonly string $text,
        public readonly int $precedence = 0
    ) {}
}
