<?php

declare(strict_types=1);

namespace davekok\lalr1;

enum SymbolType: string
{
    case ROOT = "root";
    case BRANCH = "branch";
    case LEAF = "leaf";
}
