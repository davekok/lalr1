<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpConstructorVisibility
{
    public function __construct(
        public readonly PhpConstructor $target,
        public readonly PhpVisibility  $visibility,
    ) {}

    public function readonly(): PhpConstructorReadonly
    {
        return $this->target->readonly($this->visibility);
    }

    public function param(string $name): PhpConstructorArgument
    {
        return $this->target->param($name, $this->visibility);
    }
}
