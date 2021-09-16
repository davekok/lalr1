<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use ReflectionClass;

class ParserFactory implements ParserFactoryInterface
{
    /**
     * Create a parser based on the object provided. Reflection of the object should reveal attributes.
     */
    public function createParser(object $parser, bool $debug = false): ParserInterface
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

        return new Parser($symbols, $rulesFactory->createRules(), $debug);
    }
}
