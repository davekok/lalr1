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
 *     #[InputOutput("number", "n")]
 *     #[Input("plus, text: "+")]
 *     abstract class AbstractParser implements Parser
 *     {
 *         #[Rule("n", "n + n")]
 *         public function addRule(int $n1, int $n3): int
 *         {
 *             return $n1 + $n3;
 *         }
 *     }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Rule
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
        public readonly int $precedence = 0,
    ) {}
}
