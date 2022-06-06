<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpProperty
{
    private readonly string $type;
    private readonly bool $haveDefault;
    private readonly mixed $default;

    public function __construct(
        private readonly PhpText $text,
        private readonly PhpFile $file,
        private readonly PhpClass|PhpTrait $target,
        private readonly string $name,
        private readonly PhpVisibility|false $visibility,
    ) {}

    public function constructor(...$args): PhpConstructor
    {
        return $this->target->constructor(...$args);
    }

    public function public(): PhpClassVisibility
    {
        return $this->target->public();
    }

    public function protected(): PhpClassVisibility
    {
        return $this->target->protected();
    }

    public function private(): PhpClassVisibility
    {
        return $this->target->private();
    }

    public function property(...$args): PhpProperty
    {
        return $this->target->property(...$args);
    }

    public function method(...$args): PhpMethod
    {
        return $this->target->method(...$args);
    }

    public function end(): PhpFile
    {
        return $this->file;
    }

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->haveDefault = true;
        $this->default = $value;
        return $this;
    }

    public function __toString()
    {
        $php = "";
        if ($this->visibility) {
            $php .= "{$this->visibility->value} ";
        }
        if ($this->type) {
            $php .= "$this->type ";
        }
        if ($this->name) {
            $php .= "\$$this->name";
        }
        if (isset($this->haveDefault)) {
            $php .= " = " . $this->text->value($this->default);
        }
        $php .= ";";
        return $this->text->line($php);
    }
}
