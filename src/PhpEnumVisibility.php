<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpEnumVisibility
{
    public function __construct(
        public readonly PhpEnum       $target,
        public readonly PhpVisibility $visibility,
    ) {}

    public function method(string $name): PhpMethod
    {
        return $this->target->method($name, $this->visibility);
    }
}
