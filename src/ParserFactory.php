<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use ReflectionClass;

/**
 * This factory proceduce a new parser based on an object.
 */
class ParserFactory
{
    public static function createParser(object $parser): Parser
    {
        $reflection = new ReflectionClass($parser);
        [$attribute] = $reflection->getAttributes(Symbols::class);
        $symbols = $attribute->newInstance();
        $rulesFactory = new RulesFactory($symbols);
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(Rule::class);
            if (count($attributes) === 1) {
                [$attribute] = $attributes;
                $rulesFactory->addRule(
                    $attribute->newInstance(),
                    $parser->{$method->name}(...)
                );
            }
        }
        return new Parser($symbols, $rulesFactory->createRules());
    }
}
