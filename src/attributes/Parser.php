<?php

declare(strict_types=1);

namespace davekok\parser\attributes;

use Attribute;

/**
 * Attribute to set base properties of a parser.
 *
 * # Property name
 *
 * By default the name of the parser is the base name
 * of the class.
 *
 * Default:
 *
 *     #[Parser]
 *     class MyParser
 *     {
 *     }
 *
 * Generated assets:
 *
 * - enum MyParserType
 * - enum MyParserRule
 * - class MyParserToken
 * - trait MyParserLexar
 * - trait MyParserStitcher
 *
 *
 * However, if this does not suite you, a name can be set.
 *
 * Example:
 *
 *     #[Parser("My")]
 *     class MyParser
 *     {
 *     }
 *
 * Generated assets:
 *
 * - enum MyType
 * - enum MyRule
 * - class MyToken
 * - trait MyLexar
 * - trait MyStitcher
 *
 *
 *
 * # Property valueType
 *
 * Allows you to set the type of the value field in the generated Token class.
 *
 * Example:
 *
 *                         🡦🡣🡣🡣🡣🡣🡣🡣🡣🡣🡣🡣🡣🡧
 *     #[Parser(valueType: 🡢 "int|float|null"  🡠)]
 *                         🡥🡡🡡🡡🡡🡡🡡🡡🡡🡡🡡🡡🡡🡤
 *     class MyParser
 *     {
 *     }
 *
 * Generates:
 *
 *     class MyParserToken
 *     {
 *         public function __construct(
 *             public MyParserType $type,
 *                    🡦🡣🡣🡣🡣🡣🡣🡣🡣🡣🡣🡧
 *             public 🡢 int|float|null 🡠 $value = null,
 *                    🡥🡡🡡🡡🡡🡡🡡🡡🡡🡡🡡🡤
 *         ) {}
 *     }
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Parser
{
    public function __construct(
        /**
         * The name of the parser.
         */
        public readonly string|null $name = null,

        /**
         * The type of the value property in the token class.
         */
        public readonly string $valueType = "mixed",

        /**
         * Whether to generator a lexar.
         */
        public readonly bool $lexar = false,

        /**
         * The default context, if any.
         *
         * Contexts can be used to group rules together. Setting a context activates that
         * rule group. Rules can belong to multiple contexts.
         *
         * Contexts are only used if there is more then one. Setting the current context
         * can be done with setParserContext and getParserContext to get the current context.
         *
         * If contexts are used a rule should either have a context or the default context
         * should be set.
         */
        public readonly string|null $defaultContext = null,
    ) {}
}
