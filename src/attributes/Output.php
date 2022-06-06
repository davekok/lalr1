<?php

declare(strict_types=1);

namespace davekok\parser\attributes;

use Attribute;

/**
 * With the output attribute you can declare a special type. Unlike
 * regular types you can only have one output type. Output types
 * are yielded by the parser.
 *
 * Example:
 *
 *     use davekok\parser\attributes\Output;
 *
 *     #[Output("number")]
 *     class AbstractParser implements Parser
 *     {
 *     }
 */
 #[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Output extends Type {}
