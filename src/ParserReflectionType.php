<?php

declare(strict_types=1);

namespace davekok\parser;

class ParserReflectionType
{
    public function __construct(
        public readonly int $id,
        public readonly string $key,
        public readonly string $name,
        public readonly string $text,
        public readonly bool $input,
        public readonly bool $output,
        public readonly int $precedence,
    ) {}
}
