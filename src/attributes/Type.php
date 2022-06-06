<?php

declare(strict_types=1);

namespace davekok\parser\attributes;

use Attribute;

/**
 * With the type attribute you can declare regular types. Regular types are
 * not allowed on input or output.
 *
 * Example:
 *
 *     use davekok\parser\attributes\{Input,Rule,Value};
 *
 *     #[Type("person")]
 *     #[Input("braceOpen", "'{'")]
 *     #[Input("braceClose", "'}'")]
 *     #[Input("colon", "':'")]
 *     #[Input("comma", "','")]
 *     #[Input("nameProperty", "'name'")]
 *     #[Input("nameValue", "/\"[^\"]*\"/")]
 *     #[Input("ageProperty", "'age'")]
 *     #[Input("ageValue", "/[0-9]+/")]
 *     class AbstractParser implements Parser
 *     {
 *         #[Rule("person", "braceOpen nameProperty colon nameValue comma ageProperty colon ageValue braceClose")]
 *         public function rule(string $name, int $age): Person
 *         {
 *             return new Person(name: $name, age: $age);
 *         }
 *     }
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Type
{
    public function __construct(
        public readonly string $name,
        public readonly string|null $pattern = null,
        public readonly int $precedence = 0,
        public readonly array|string|null $context = null,
    ) {}
}
