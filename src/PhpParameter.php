<?php

declare(strict_types=1);

namespace davekok\parser;

use ReflectionClass;

class PhpParameter
{
    private string|false $type = false;
    private bool $haveDefault = false;
    private mixed $default;

    public function __construct(
        public readonly PhpText   $text,
        public readonly PhpFile   $file,
        public readonly PhpMethod $parent,
        public readonly string    $name,
    ) {}

    public function type(string|ReflectionClass $type): static
    {
        $this->type = $this->file->reference($type);
        return $this;
    }

    public function default(mixed $default): static
    {
        $this->haveDefault = true;
        $this->default = $default;
        return $this;
    }

    public function param(...$args): static
    {
        return $this->parent->param(...$args);
    }

    public function body(...$args): PhpClass|PhpEnum|PhpTrait
    {
        return $this->parent->body(...$args);
    }

    public function end(): PhpFile
    {
        return $this->file;
    }

    public function __toString(): string
    {
        $php = "";
        if ($this->type) {
            $php .= "$this->type ";
        }
        $php .= "\$$this->name";
        if ($this->haveDefault) {
            $php .= " = " . $this->text->value($this->default);
        }
        return $php;
    }
}
