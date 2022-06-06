<?php

declare(strict_types=1);

namespace davekok\parser;

use Exception;
use ReflectionClass;

class PhpMethod
{
    private bool $final = false;
    private bool $abstract = false;
    private string|false $comment = false;
    private string|false $returns = false;
    protected array $params = [];
    private array $lines = [];

    public function __construct(
        public readonly PhpText                   $text,
        public readonly PhpFile                   $file,
        public readonly PhpClass|PhpEnum|PhpTrait $parent,
        public readonly string                    $name,
        public readonly PhpVisibility|false       $visibility = false,
    ) {}

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function final(): self
    {
        if ($this->abstract) {
            throw new Exception("Method can't both be abstract and final.");
        }
        $this->final = true;
        return $this;
    }

    public function abstract(): self
    {
        if ($this->final) {
            throw new Exception("Method can't both be abstract and final.");
        }
        $this->abstract = true;
        return $this;
    }

    public function param(string $name): PhpParameter
    {
        return $this->params[] = new PhpParameter($this->text, $this->file, $this, $name);
    }

    public function returns(string|ReflectionClass $type): self
    {
        $this->returns = $this->file->reference($type);
        return $this;
    }

    public function body(string|iterable|null $body = null): PhpClass|PhpEnum|PhpTrait
    {
        if ($body === null) {
            $this->lines = [];
        } else {
            $this->lines = is_iterable($body) ? $body : explode("\n", rtrim($body, "\n"));
        }
        return $this->parent;
    }

    public function end(): PhpFile
    {
        return $this->file;
    }

    public function __toString()
    {
        $php = "";
        if ($this->comment) {
            $php .= $this->text->blockComment($this->comment);
        }
        $php .= $this->text->indent();
        if ($this->abstract) {
            $php .= "abstract ";
        } else if ($this->final) {
            $php .= "final ";
        }
        if ($this->visibility) {
            $php .= "{$this->visibility->value} ";
        }
        $php .= "function $this->name(";
        $i = 0;
        foreach ($this->params as $param) {
            if ($i++) $php .= ", ";
            $php .= (string)$param;
        }
        $php .= ")";
        if ($this->returns) {
            $php .= ": $this->returns";
        }
        if (count($this->lines) > 0) {
            $php .= $this->text->bodyOpen();
            foreach ($this->lines as $line) {
                $php .= $this->text->line($line);
            }
            $php .= $this->text->bodyClose();
        } else {
            $php .= $this->text->emptyBody();
        }
        return $php;
    }
}
