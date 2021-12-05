<?php

declare(strict_types=1);

namespace davekok\lalr1;

interface Rules
{
    public function setParser(Parser $parser): void;
}
