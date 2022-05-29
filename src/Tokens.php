<?php

declare(strict_types=1);

namespace davekok\parser;

use Exception;

class Tokens
{
    public function __construct(
        private array $tokens = [],
    ) {}

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
}
