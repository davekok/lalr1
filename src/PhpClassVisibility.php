<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpClassVisibility
{
    public function __construct(
        public readonly PhpClass|PhpTrait $target,
        public readonly PhpVisibility     $visibility,
    ) {}

    public function property(string $name): PhpProperty
    {
        return $this->target->property($name, $this->visibility);
    }

    public function constructor(): PhpConstructor
    {
        return $this->target->constructor($this->visibility);
    }

    public function method(string $name): PhpMethod
    {
        return $this->target->method($name, $this->visibility);
    }
}
