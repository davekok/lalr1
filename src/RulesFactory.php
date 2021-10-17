<?php

declare(strict_types=1);

namespace davekok\lalr1;

use davekok\lalr1\attributes\Rule as RuleAttribute;
use davekok\lalr1\attributes\Symbol as SymbolAttribute;
use davekok\lalr1\attributes\Symbols as SymbolsAttribute;
use davekok\lalr1\attributes\Solution as SolutionAttribute;
use ReflectionClass;

class RulesFactory
{
    public function createRules(ReflectionClass $rulesClass): Rules
    {
        $symbolsAttributes = $rulesClass->getAttributes(SymbolsAttribute::class);
        if (count($symbolsAttributes) === 0) {
            throw new RulesException("Symbols attribute is missing");
        }

        $symbols = [];
        $haveRoot = false;
        foreach ($symbolsAttributes[0]->newInstance()->symbols as $index => $symbolAttr) {
            $key = Key::createKey($index);
            if ($symbolAttr->type === SymbolType::ROOT) {
                if ($haveRoot === true) {
                    throw new RulesException("There can be only one root symbol.");
                }
                $haveRoot = false;
            }
            $symbols[$symbolAttr->name] = new Symbol($symbolAttr->type, $key, $symbolAttr->name, $symbolAttr->precedence);
        }

        $solutionMethod = null;
        $rules = [];
        foreach ($rulesClass->getMethods() as $method) {
            foreach ($method->getAttributes() as $attr) {
                switch (true) {
                    case $attr->getName() === SolutionAttribute::class:
                        if ($solutionMethod !== null) {
                            throw new RulesException("There can be only one solution method.");
                        }
                        $solutionMethod = $method;
                        break;

                    case $attr->getName() === RuleAttribute::class:
                        $ruleAttr = $attr->newInstance();

                        $key = Key::createKey();
                        $precedence = $ruleAttr->precedence;
                        foreach (explode(" ", $ruleAttr->text) as $symbolName) {
                            $symbol = $symbols[$symbolName];
                            if ($symbol == null) {
                                throw new RulesException("No such symbol '$symbolName'");
                            }
                            $key .= $symbol->key;
                            if ($precedence === 0 && $symbol->type === SymbolType::LEAF && $symbol->precedence != 0) {
                                $precedence = $symbol->precedence;
                            }
                        }
                        $rules[$key] = new Rule($key, $ruleAttr->text, $precedence, $method);
                        break;
                }
            }
        }

        if ($solutionMethod === null) {
            throw new RulesException("Solution method is missing.");
        }

        return new Rules($symbols, $rules, $solutionMethod);
    }
}
