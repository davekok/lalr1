<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class RulesFactory
{
    private array $rules = [];

    public function __construct(
        private readonly Symbols $symbols
    ) {}

    public function createRules(): Rules
    {
        return new Rules($this->rules);
    }

    public function addRule(Rule $rule, callable $reduce): void
    {
        $precedence  = 0;
        $symbolKey   = "";
        $symbolNames = explode(" ", $rule->text);
        $count       = count($symbolNames);
        $lastKey     = $count - 1;

        // By default the rule's precedence is set by left most leaf symbol, if it
        // has its precedence set. However, if the last symbol is a number instead
        // of a name. It is used as the rule's precedence instead.

        foreach ($symbolNames as $key => $symbolName) {
            if ($key === $lastKey && ($p = filter_var($symbolName, FILTER_VALIDATE_INT)) !== false) {
                $precedence = $p;
                break;
            }

            $symbol     = $this->symbols->getByName($symbolName);
            $symbolKey .= $symbol->key;

            if ($precedence === 0 && $symbol instanceof LeafSymbol && $symbol->precedence > 0) {
                $precedence = $symbol->precedence;
            }
        }

        $this->rules[$symbolKey] = new RuleStruct($symbolKey, $precedence, $reduce, $rule->text);
    }
}
