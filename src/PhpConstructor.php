<?php

declare(strict_types=1);

namespace davekok\parser;

class PhpConstructor extends PhpMethod
{
    public function __construct(PhpText $text, PhpFile $file, PhpClass $parent, PhpVisibility|false $visibility = false)
    {
        parent::__construct($text, $file, $parent, "__construct", $visibility);
    }

    public function public(): PhpConstructorVisibility
    {
        return new PhpConstructorVisibility($this, PhpVisibility::public);
    }

    public function protected(): PhpConstructorVisibility
    {
        return new PhpConstructorVisibility($this, PhpVisibility::protected);
    }

    public function private(): PhpConstructorVisibility
    {
        return new PhpConstructorVisibility($this, PhpVisibility::private);
    }

    public function readonly(PhpVisibility|false $visibility = false): PhpConstructorReadonly
    {
        return new PhpConstructorReadonly($this, $visibility);
    }

    public function param(string $name, PhpVisibility|false $visibility = false, bool $readonly = false): PhpConstructorParameter
    {
        return $this->params[] = new PhpConstructorParameter($this->text, $this->file, $this, $name, $visibility, $readonly);
    }
}
