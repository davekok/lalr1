<?php

declare(strict_types=1);

namespace davekok\parser;

interface TypeEnum
{
    public function id(): int;
    public function name(): string;
    public function key(): string;
    public function pattern(): string;
    public function input(): bool;
    public function output(): bool;
    public function precedence(): int;
}
