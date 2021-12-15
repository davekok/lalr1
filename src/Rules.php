<?php

declare(strict_types=1);

namespace davekok\parser;

interface Rules
{
    public function setParser(Parser $parser): void;
}
