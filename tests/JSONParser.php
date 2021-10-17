<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\attributes\{Rule,Solution,Symbol,Symbols};
use davekok\lalr1\SymbolType;
use davekok\lalr1\Token;
use Exception;
use stdClass;

#[Symbols(
    new Symbol(SymbolType::LEAF, "null"),
    new Symbol(SymbolType::LEAF, "boolean"),
    new Symbol(SymbolType::LEAF, "number"),
    new Symbol(SymbolType::LEAF, "string"),
    new Symbol(SymbolType::LEAF, "opening-brace"),
    new Symbol(SymbolType::LEAF, "closing-brace"),
    new Symbol(SymbolType::LEAF, "opening-bracket"),
    new Symbol(SymbolType::LEAF, "closing-bracket"),
    new Symbol(SymbolType::LEAF, "comma"),
    new Symbol(SymbolType::LEAF, "colon", 1),
    new Symbol(SymbolType::BRANCH, "object"),
    new Symbol(SymbolType::BRANCH, "array"),
    new Symbol(SymbolType::BRANCH, "properties"),
    new Symbol(SymbolType::BRANCH, "elements"),
    new Symbol(SymbolType::BRANCH, "key"),
    new Symbol(SymbolType::ROOT, "value")
)]
class JSONParser
{
    public readonly Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(string $buffer): mixed
    {
        try {
            foreach (new JSONScanner($this->parser, $buffer) as $token) {
                $this->parser->pushToken($token);
            }
            return $this->parser->endOfTokens();
        } catch (\Throwable $e) {
            echo "\n";
            print_r($this->parser->getDebugLog());
            throw $e;
        }
    }

    #[Solution]
    public function solution(Token $nullToken): voi
    {
    }

    #[Rule("null")]
    public function promoteNull(Token $nullToken): Token
    {
        return $this->parser->createToken("value", $nullToken->value);
    }

    #[Rule("boolean")]
    public function promoteBoolean(Token $booleanToken): Token
    {
        return $this->parser->createToken("value", $booleanToken->value);
    }

    #[Rule("number")]
    public function promoteNumber(Token $numberToken): Token
    {
        return $this->parser->createToken("value", $numberToken->value);
    }

    #[Rule("string")]
    public function promoteString(Token $stringToken): Token
    {
        return $this->parser->createToken("value", $stringToken->value);
    }

    #[Rule("object")]
    public function promoteObject(Token $objectToken): Token
    {
        return $this->parser->createToken("value", $objectToken->value);
    }

    #[Rule("array")]
    public function promoteArray(Token $arrayToken): Token
    {
        return $this->parser->createToken("value", $arrayToken->value);
    }

    #[Rule("opening-bracket closing-bracket")]
    public function emptyArray(Token $openingBracket, Token $closingBracket): Token
    {
        return $this->parser->createToken("array", []);
    }

    #[Rule("opening-bracket value")]
    public function startArray(Token $openingBracket, Token $value): Token
    {
        $elementsValue = [$value->value];
        return $this->parser->createToken("elements", $elementsValue);
    }

    #[Rule("elements comma value")]
    public function addElement(Token $elements, Token $comma, Token $value): Token
    {
        $elements->value[] = $value->value;
        return $elements;
    }

    #[Rule("elements closing-bracket")]
    public function endArray(Token $elements, Token $closingBracket): Token
    {
        return $this->parser->createToken("array", $elements->value);
    }

    #[Rule("opening-brace closing-brace")]
    public function emptyObject(Token $openingBrace, Token $closingBrace): Token
    {
        return $this->parser->createToken("object", new stdClass);
    }

    #[Rule("opening-brace key value")]
    public function startObject(Token $openingBrace, Token $key, Token $value): Token
    {
        $propertiesValue       = new stdClass;
        $key                   = $key->value;
        $propertiesValue->$key = $value->value;

        return $this->parser->createToken("properties", $propertiesValue);
    }

    #[Rule("properties comma key value")]
    public function addProperty(Token $properties, Token $comma, Token $key, Token $value): Token
    {
        $key                     = $key->value;
        $properties->value->$key = $value->value;

        return $properties;
    }

    #[Rule("properties closing-brace")]
    public function closeObject(Token $properties, Token $closingBrace): Token
    {
        return $this->parser->createToken("object", $properties->value);
    }

    #[Rule("string colon")]
    public function promoteToKey(Token $string, Token $colon): Token
    {
        return $this->parser->createToken("key", $string->value);
    }
}
