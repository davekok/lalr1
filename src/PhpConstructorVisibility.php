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

    public function arg(string $name): PhpConstructorArgument
    {
        return $this->target->arg($name, $this->visibility);
    }
}
