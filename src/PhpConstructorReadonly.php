<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpConstructorReadonly
{
    public function __construct(
        public readonly PhpConstructor      $target,
        public readonly PhpVisibility|false $visibility = false,
    ) {}

    public function arg(string $name): PhpConstructorArgument
    {
        return $this->target->arg($name, $this->visibility, readonly: true);
    }
}
