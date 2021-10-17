<?php

declare(strict_types=1);

namespace davekok\lalr1;

use Exception;

class Tokens
{
    private array $tokens = [];

    public function reset(): void
    {
        $this->tokens = [];
    }

    public function get(int $offset): Token
    {
        return $this->tokens[$offset];
    }

    public function push(Token $token): void
    {
        $this->tokens[] = $token;
    }

    public function pop(): Token
    {
        return array_pop($this->tokens);
    }

    public function last(): Token
    {
        return end($this->tokens);
    }

    public function rangeFrom(int $offset): array
    {
        return array_slice($this->tokens, $offset);
    }

    public function replace(int $offset, int $size, Token $token): void
    {
        array_splice($this->tokens, $offset, $size, [$token]);
    }

    public function count()
    {
        return count($this->tokens);
    }

    public function __toString(): string
    {
        return json_encode(["count" => $this->count(), "tokens" => $this->tokens], JSON_THROW_ON_ERROR);
    }
}