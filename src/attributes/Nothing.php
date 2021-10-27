<?php

declare(strict_types=1);

namespace davekok\lalr1\attributes;

use Attribute;

/**
 * Set this attribute on a method that processes when nothing is passed in.
 *
 * Example:
 *
 *     use davekok\larl1\attributes\Nothing;
 *
 *     class MyRules
 *     {
 *         #[Nothing]
 *         public function nothing(): void
 *         {
 *             // do something smart
 *         }
 *     }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Nothing {}
