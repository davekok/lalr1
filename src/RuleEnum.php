<?php

declare(strict_types=1);

namespace davekok\parser;

interface RuleEnum
{
    public function name(): string;
    public function key(): string;
    public function rule(): string;
    public function precedence(): int;
}
