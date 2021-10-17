<?php

declare(strict_types=1);

namespace davekok\lalr1\attributes;

use Attribute;

/**
 * Set this attribute on a method that processes the solution.
 *
 * Example:
 *
 *     use davekok\larl1\attributes\Solution;
 *
 *     class MyRules
 *     {
 *         #[Solution]
 *         public function solution(mixed $value): void
 *         {
 *             // do something smart
 *         }
 *     }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Solution {}
