<?php

declare(strict_types=1);

namespace davekok\parser;

use Exception;
use ReflectionClass;

class PhpCodeStyle
{
    public function __construct(
        public string $indent = "    ",
        public string $nl = "\n",
        public bool $braceNextLine = true,
        public bool $phpClose = false,
        public bool $use = true,
        public bool $declareOnHeader = false,
        public bool $blackLineAfterHeader = true,
    ) {}
}
