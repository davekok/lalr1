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
 *     #[Type("person", "p")]
 *     #[Input("braceOpen", "{")]
 *     #[Input("braceClose", "}")]
 *     #[Input("colon", ":")]
 *     #[Input("comma", ",")]
 *     #[Input("nameProperty", "name")]
 *     #[Input("nameValue", "$name")]
 *     #[Input("ageProperty", "age")]
 *     #[Input("ageValue", "$age")]
 *     class AbstractParser implements Parser
 *     {
 *         #[Rule("p", "{ name : $name , age : $age }")]
 *         public function rule(string $name1, int $age8): Person
 *         {
 *             return new Person(name: $name1, age: $age8);
 *         }
 *     }
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Type
{
    public function __construct(
        public readonly string $name,
        public readonly string $text,
        public readonly int $precedence = 0,
    ) {}
}
