<?php

declare(strict_types=1);

namespace davekok\parser;

use Exception;

class PhpText
{
    public function __construct(
        public readonly PhpCodeStyle $style,
    ) {}

    private int $indentLevel = 0;

    public function indinc(): string
    {
        return str_repeat($this->style->indent, $this->indentLevel++);
    }

    public function inddec(): string
    {
        return str_repeat($this->style->indent, --$this->indentLevel);
    }

    public function indent(): string
    {
        return str_repeat($this->style->indent, $this->indentLevel);
    }

    public function line(string $code = ""): string
    {
        if (strlen($code) === 0) {
            return "{$this->style->nl}";
        }
        return match (true) {
            str_ends_with($code, "{") => "{$this->indinc()}$code{$this->style->nl}",
            str_ends_with($code, "}") || str_ends_with($code, "};") => "{$this->inddec()}$code{$this->style->nl}",
            default => "{$this->indent()}$code{$this->style->nl}",
        };
    }

    public function blockComment(string $comment): string
    {
        $php = $this->line("/**");
        foreach (explode("\n", $comment) as $line) {
            $php .= $this->line(" * " . rtrim($line));
        }
        $php .= $this->line(" */");
        return $php;
    }

    public function bodyOpen(): string
    {
        if ($this->style->braceNextLine) {
            return "{$this->style->nl}{$this->indinc()}{{$this->style->nl}";
        }
        $this->indentLevel++;
        return " {{$this->style->nl}";
    }

    public function bodyClose(): string
    {
        return "{$this->inddec()}}{$this->style->nl}";
    }

    public function emptyBody(): string
    {
        return " {}{$this->style->nl}";
    }

    public function parts(array $parts): string
    {
        $i = 0;
        $php = "";
        foreach ($parts as $part) {
            if ($i++) $php .= $this->style->nl;
            $php .= (string)$part;
        }
        return $php;
    }

    public function value(mixed $value): string
    {
        return match (true) {
            is_string($value) => "\"$value\"",
            is_int($value), is_float($value) => (string)$value,
            $value === true => "true",
            $value === false => "false",
            $value === null => "null",
            is_array($value) && count($value) === 0 => "[]",
            default => throw new Exception("unsupported value: " . var_export($value, true)),
        };
    }
}
