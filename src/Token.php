<?php

declare(strict_types=1);

namespace davekok\parser;

class Token
{
    public function __construct(
        public readonly Symbol $symbol,
        public mixed $value = null,
    ) {}

    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }
}
