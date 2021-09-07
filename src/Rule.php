<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class Rule
{
    public readonly string $key;
    public readonly ?int $specialPrecedence;
    public readonly callable $reduce;

    public function __construct(callable $reduce, ?int $specialPrecedence, Type ...$types)
    {
        $key = "";
        foreach ($types as $type) {
            $key .= $type->key;
        }
        $this->key = $key;
        $this->reduce = $reduce;
        $this->specialPrecedence = $specialPrecedence;
    }
}
