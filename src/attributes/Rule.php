<?php

declare(strict_types=1);

namespace davekok\parser\attributes;

use Attribute;

/**
 * Set this attribute on a method to declare a rule.
 *
 * Example:
 *
 *     use davekok\parser\attributes\{Rule,Input,Output};
 *
 *     #[InputOutput("number")]
 *     #[Input("plus)]
 *     class MyParser
 *     {
 *         #[Rule("number", "number plus number")]
 *         public function addRule(int $number1, int $number2): int
 *         {
 *             return $number1 + $number2;
 *         }
 *     }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Rule
{
    public function __construct(
        public readonly string $type,
        public readonly string $rule,
        public readonly int $precedence = 0,
        public readonly array|string|null $context = null,
    ) {}
}
