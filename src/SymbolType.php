<?php

declare(strict_types=1);

namespace davekok\parser;

enum SymbolType: string
{
    case ROOT = "root";
    case BRANCH = "branch";
    case LEAF = "leaf";
}
