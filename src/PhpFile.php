<?php

declare(strict_types=1);

namespace davekok\parser;

use Exception;
use ReflectionClass;
use ReflectionEnum;

class PhpFile
{
    private const BUILTINS = [
        "mixed",
        "void",
        "never",
        "null",
        "bool",
        "int",
        "float",
        "string",
        "object",
        "array",
        "iterable",
        "callable",
        "self",
        "static",
        "parent",
    ];

    private readonly PhpText $text;
    private string|false $comment = false;
    private string|false $namespace = false;
    private bool $strictTypes = true;
    private array $references = [];
    private array $parts = [];

    public function __construct(
        public readonly string $name,
        public readonly PhpCodeStyle $style = new PhpCodeStyle(),
    ) {
        $this->text = new PhpText($style);
    }

    public function comment(string|false $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function namespace(string|false $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function strictTypes(bool $strictTypes): self
    {
        $this->strictTypes = $strictTypes;
        return $this;
    }

    public function class(string $name): PhpClass
    {
        return $this->parts[] = new PhpClass($name, $this, $this->text);
    }

    public function enum(string $name): PhpEnum
    {
        return $this->parts[] = new PhpEnum($name, $this, $this->text);
    }

    public function trait(string $name): PhpTrait
    {
        return $this->parts[] = new PhpTrait($name, $this, $this->text);
    }

    public function end(): PhpFile
    {
        return $this;
    }

    public function __toString(): string
    {
        if ($this->strictTypes && $this->style->declareOnHeader) {
            $php = $this->text->line("<?php declare(strict_types=1);");
        } else {
            $php = $this->text->line("<?php");
        }
        if ($this->style->blackLineAfterHeader) {
            $php .= $this->text->line();
        }
        if ($this->comment !== false) {
            $php .= $this->text->blockComment($this->comment) . $this->text->line();
        }
        if ($this->strictTypes && !$this->style->declareOnHeader) {
            $php .= $this->text->line("declare(strict_types=1);");
            $php .= $this->text->line();
        }
        if ($this->namespace !== false) {
            $php .= $this->text->line("namespace $this->namespace;");
            $php .= $this->text->line();
        }
        if ($this->style->use && count($this->references) > 0) {
            sort($this->references);
            foreach ($this->references as $reference) {
                $php .= $this->text->line("use $reference;");
            }
            $php .= $this->text->line();
        }
        $php .= $this->text->parts($this->parts);
        $php .= $this->style->phpClose ? $this->text->line("?>") : "";
        return $php;
    }

    public function reference(string|ReflectionClass $reference): string
    {
        if (is_string($reference)) {
            if (str_starts_with($reference, "?")) {
                return "?" . $this->reference(substr($reference, 1));
            }
            if (str_contains($reference, "|")) {
                $refs = "";
                $i = 0;
                foreach (explode("|", $reference) as $ref) {
                    if ($i++) $refs .= "|";
                    $refs .= $this->reference($ref);
                }
                return $refs;
            }
            $reference = trim($reference);
            if (in_array($reference, self::BUILTINS)) {
                return $reference;
            }
            $reference = match (true) {
                class_exists($reference) || interface_exists($reference) => new ReflectionClass($reference),
                enum_exists($reference) => new ReflectionEnum($reference),
                default => throw new Exception("Unknown reference $reference"),
            };
        }

        if ($this->style->use) {
            if ($reference->getNamespaceName() !== $this->namespace) {
                $this->references[$reference->getName()] = $reference->getName();
            }
            return $reference->getShortName();
        }

        return "\\" . $reference->getName();
    }
}
