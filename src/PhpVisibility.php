<?php

declare(strict_types=1);

namespace davekok\parser;

enum PhpVisibility: string {
    case private = "private";
    case protected = "protected";
    case public = "public";
}
