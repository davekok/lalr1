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
 *     #[Input("number")]
 *     class AbstractParser implements Parser
 *     {
 *     }
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Input extends Type {}
