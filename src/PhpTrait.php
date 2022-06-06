<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpTrait
{
    private string|false $comment = false;
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

    public function public(): PhpClassVisibility
    {
        return new PhpClassVisibility($this, PhpVisibility::public);
    }

    public function protected(): PhpClassVisibility
    {
        return new PhpClassVisibility($this, PhpVisibility::protected);
    }

    public function private(): PhpClassVisibility
    {
        return new PhpClassVisibility($this, PhpVisibility::private);
    }

    public function property(string $name, PhpVisibility|false $visibility = false): PhpProperty
    {
        return $this->parts[] = new PhpProperty($this->text, $this->file, $this, $name, $visibility);
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
        $php .= "trait $this->name";
        $php .= $this->text->bodyOpen();
        $php .= $this->text->parts($this->parts);
        $php .= $this->text->bodyClose();
        return $php;
    }
}
