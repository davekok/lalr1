<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class Token
{
    public function __construct(
        public readonly Symbol $symbol,
        public readonly mixed $value = null
    ) {}
}
