<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\attributes\{Rule,Solution,Symbol,Symbols};
use davekok\lalr1\Parser;
use davekok\lalr1\SymbolType;
use davekok\lalr1\Token;
use Exception;
use stdClass;

#[Symbols(
    new Symbol(SymbolType::LEAF, "null"),
    new Symbol(SymbolType::LEAF, "boolean"),
    new Symbol(SymbolType::LEAF, "number"),
    new Symbol(SymbolType::LEAF, "string"),
    new Symbol(SymbolType::LEAF, "{"),
    new Symbol(SymbolType::LEAF, "}"),
    new Symbol(SymbolType::LEAF, "["),
    new Symbol(SymbolType::LEAF, "]"),
    new Symbol(SymbolType::LEAF, ","),
    new Symbol(SymbolType::LEAF, ":", 1),
    new Symbol(SymbolType::BRANCH, "object"),
    new Symbol(SymbolType::BRANCH, "array"),
    new Symbol(SymbolType::BRANCH, "properties"),
    new Symbol(SymbolType::BRANCH, "elements"),
    new Symbol(SymbolType::BRANCH, "key"),
    new Symbol(SymbolType::ROOT, "value")
)]
class JSONRules
{
    public readonly Parser $parser;
    public mixed $solution;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->parser->setRulesObject($this);
    }

    #[Solution]
    public function solution(mixed $solution): void
    {
        $this->solution = $solution;
    }

    #[Rule("null")]
    public function promoteNull(array $tokens): Token
    {
        return $this->parser->createToken("value", $tokens[0]->value);
    }

    #[Rule("boolean")]
    public function promoteBoolean(array $tokens): Token
    {
        return $this->parser->createToken("value", $tokens[0]->value);
    }

    #[Rule("number")]
    public function promoteNumber(array $tokens): Token
    {
        return $this->parser->createToken("value", $tokens[0]->value);
    }

    #[Rule("string")]
    public function promoteString(array $tokens): Token
    {
        return $this->parser->createToken("value", $tokens[0]->value);
    }

    #[Rule("object")]
    public function promoteObject(array $tokens): Token
    {
        return $this->parser->createToken("value", $tokens[0]->value);
    }

    #[Rule("array")]
    public function promoteArray(array $tokens): Token
    {
        return $this->parser->createToken("value", $tokens[0]->value);
    }

    #[Rule("[ ]")]
    public function emptyArray(array $tokens): Token
    {
        return $this->parser->createToken("array", []);
    }

    #[Rule("[ value")]
    public function startArray(array $tokens): Token
    {
        return $this->parser->createToken("elements", [$tokens[1]->value]);
    }

    #[Rule("elements , value")]
    public function addElement(array $tokens): Token
    {
        $tokens[0]->value[] = $tokens[2]->value;
        return $elements;
    }

    #[Rule("elements ]")]
    public function endArray(array $tokens): Token
    {
        return $this->parser->createToken("array", $tokens[0]->value);
    }

    #[Rule("{ }")]
    public function emptyObject(array $tokens): Token
    {
        return $this->parser->createToken("object", new stdClass);
    }

    #[Rule("{ key value")]
    public function startObject(array $tokens): Token
    {
        $propertiesValue       = new stdClass;
        $key                   = $tokens[1]->value;
        $propertiesValue->$key = $tokens[2]->value;

        return $this->parser->createToken("properties", $propertiesValue);
    }

    #[Rule("properties , key value")]
    public function addProperty(array $tokens): Token
    {
        $key                     = $tokens[2]->value;
        $properties->value->$key = $tokens[3]->value;

        return $properties;
    }

    #[Rule("properties }")]
    public function closeObject(array $tokens): Token
    {
        return $this->parser->createToken("object", $tokens[0]->value);
    }

    #[Rule("string :")]
    public function promoteToKey(array $tokens): Token
    {
        return $this->parser->createToken("key", $tokens[0]->value);
    }
}
