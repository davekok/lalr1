<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

interface ParserInterface
{
    /**
     * Create a new token that you can push to the parser.
     */
    public function createToken(string $name, mixed $value = null): Token;

    /**
     * Push a previously created token.
     *
     * @throws ParserException
     */
    public function pushToken(Token $token): void;

    /**
     * Signal end of tokens and get the solution.
     *
     * @throws NoSolutionParserException
     */
    public function endOfTokens(): mixed;

    /**
     * Get the debug log, only if parser has been created with debug set to true.
     */
    public function getDebugLog(): array;
}
