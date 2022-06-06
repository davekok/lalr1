<?php

declare(strict_types=1);

namespace davekok\parser;

use Exception;

class PhpEnum
{
    private string|false $comment = false;
    private array|false $implements = false;
    private string|false $backed = false;
    private array $cases = [];
    private array $parts = [];

    public function __construct(
        public readonly string   $name,
        private readonly PhpFile $file,
        private readonly PhpText $text,
    ) {}

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function implements(string ...$implements): self
    {
        $this->implements = [];
        foreach ($implements as $reference) {
            $this->implements[] = $this->file->reference($reference);
        }
        return $this;
    }

    public function stringBacked(): self
    {
        if ($this->backed !== false) {
            throw new Exception("A enum can't be both string backed and int backed.");
        }
        $this->backed = "string";
        return $this;
    }

    public function intBacked(): self
    {
        if ($this->backed !== false) {
            throw new Exception("A enum can't be both string backed and int backed.");
        }
        $this->backed = "int";
        return $this;
    }

    public function cases(iterable $cases): self
    {
        foreach ($cases as $key => $value) {
            if (is_int($key)) {
                $this->case($value, null);
            } else {
                $this->case($key, $value);
            }
        }
        return $this;
    }

    public function case(string $name, string|int|null $value = null): self
    {
        if ($this->backed === "string" && is_string($value) === false) {
            throw new Exception("String backed enums require a string value for each case.");
        } else if ($this->backed === "int" && is_int($value) === false) {
            throw new Exception("Int backed enums require an int value for each case.");
        } else if ($this->backed === false && is_null($value) === false) {
            throw new Exception("Unbacked enums can't have value.");
        }
        $this->cases[$name] = $value;
        return $this;
    }

    public function public(): PhpEnumVisibility
    {
        return new PhpEnumVisibility($this, PhpVisibility::public);
    }

    public function protected(): PhpEnumVisibility
    {
        return new PhpEnumVisibility($this, PhpVisibility::protected);
    }

    public function private(): PhpEnumVisibility
    {
        return new PhpEnumVisibility($this, PhpVisibility::private);
    }

    public function method(string $name, PhpVisibility|false $visibility = false): PhpMethod
    {
        return $this->parts[] = new PhpMethod($this->text, $this->file, $this, $name, $visibility);
    }

    public function end(): PhpFile
    {
        return $this->file;
    }

    public function __toString(): string
    {
        $php = "";
        if ($this->comment) {
            $php .= $this->text->blockComment($this->comment);
        }
        $php .= $this->text->indent();
        $php .= "enum $this->name";
        if ($this->backed) {
            $php .= ": $this->backed";
        }
        if ($this->implements) {
            $php .= " implements " . implode(", ", $this->implements);
        }
        $php .= $this->text->bodyOpen();
        foreach ($this->cases as $name => $value) {
            $php .= $this->text->line("case $name" . match (true) {
                    is_string($value) => " = \"$value\";",
                    is_int($value) => " = $value;",
                    is_null($value) => ";",
                });
        }
        if (count($this->parts) > 0) {
            $php .= $this->text->line();
        }
        $php .= $this->text->parts($this->parts);
        $php .= $this->text->bodyClose();
        return $php;
    }
}
