<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpConstructorParameter extends PhpParameter
{
    private PhpVisibility|false $visibility;
    private bool $readonly;

    public function __construct(
        PhpText             $text,
        PhpFile             $file,
        PhpConstructor      $parent,
        string              $name,
        PhpVisibility|false $visibility = false,
        bool                $readonly = false,
    )
    {
        parent::__construct($text, $file, $parent, $name);
        $this->visibility = $visibility;
        $this->readonly = $readonly;
    }

    public function public(): PhpConstructorVisibility
    {
        return new PhpConstructorVisibility($this->parent, PhpVisibility::public);
    }

    public function protected(): PhpConstructorVisibility
    {
        return new PhpConstructorVisibility($this->parent, PhpVisibility::protected);
    }

    public function private(): PhpConstructorVisibility
    {
        return new PhpConstructorVisibility($this->parent, PhpVisibility::private);
    }

    public function readonly(): PhpConstructorReadonly
    {
        return new PhpConstructorReadonly($this->parent);
    }

    public function __toString(): string
    {
        $php = "";
        if ($this->visibility) {
            $php .= "{$this->visibility->value} ";
        }
        if ($this->readonly) {
            $php .= "readonly ";
        }
        return $php . parent::__toString();
    }
}
