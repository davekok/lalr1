<?php

declare(strict_types=1);

namespace davekok\parser\attributes;

use Attribute;

/**
 * With the inputoutput attribute you can declare a special type.
 * Unlike regular types you can only have one input output type
 * and is mutually exclusive with the output attribute.
 *
 * An inputoutput type can be used both on input and on output.
 *
 * Example:
 *
 *     use davekok\parser\attributes\{InputOutput};
 *
 *     #[InputOutput("number")]
 *     class AbstractParser implements Parser
 *     {
 *     }
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class InputOutput extends Type {}
