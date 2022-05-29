<?php

declare(strict_types=1);

namespace davekok\parser\attributes;

use Attribute;

/**
 * With the input attribute you can declare a special type.
 *
 * Only input types may be used for input.
 *
 * Example:
 *
 *     use davekok\parser\attributes\Input;
 *
 *     #[Input("plus", "+", 0)]
 *     class AbstractParser implements Parser
 *     {
 *     }
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Input extends Type {}
