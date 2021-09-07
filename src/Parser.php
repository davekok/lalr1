<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use LogicException;
use RuntimeException;

/**
 * This class implements the Look Ahead, Left to right, Right most derivation algorithm.
 *
 * It works by pushing tokens. Then the right most (last pushed) token(s) are checked against
 * the rules, excluding the last pushed token as it is used as the look ahead token. If a rule
 * is matched, its reduce function is called and the matched tokens are replaced with the
 * token returned by the reduce function.
 *
 * When there are no more tokens push, call endOfTokens.
 *
 * A solution is found when at the end of tokens, through reducing, only one token remains having
 * the final token type.
 */
class Parser
{
    /**
     * If a solution has been found, this property contains the value of the final token.
     */
    public mixed $value;

    private array $tokens = [];

    public function __construct(
        private readonly Rules $rules,
        private readonly Types $types
    ) {
    }

    /**
     * Create a new token.
     */
    public function createToken(string $name, mixed $value = null): Token
    {
        $type = $this->types->getByName($name) ?? null;
        if ($type === null) {
            throw new Exception("No such type '$name'.");
        }
        return new Token($type, $value);
    }

    /**
     * Push a token.
     */
    public function pushToken(Token $token): void
    {
        if ($token->type !== $this->types->finalType) {
            throw new LogicException("Tokens of the final token type should not be pushed.");
        }

        $this->tokens[] = $token;

        $this->reduce(false);
    }

    /**
     * Signal end of tokens has been reached.
     */
    public function endOfTokens(): void
    {
        $this->reduce(true);
    }

    private function reduce(bool $endOfTokens): void
    {
        for (;;) {
            $l = count($this->tokens);

            if ($l === 0) {
                // Edge case: In case the first push is null.
                if ($endOfTokens === true) {
                    throw new LogicException("End of tokens reached, but no valid solution.");
                }

                throw new RuntimeException("Invalid internal state.");
            }

            if ($l === 1) {
                // Edge case: first push should be ignored as it only arms the look ahead.
                if ($endOfTokens === false) {
                    return;
                }

                $finalToken = array_pop($this->tokens);

                // The final token should be of final token type.
                if ($finalToken->type !== $this->types->finalType) {
                    throw new LogicException("End of tokens reached, but no valid solution.");
                }

                $this->value = $finalToken->value;

                return;
            }

            // Skip the lookahead token until the end has been reached.
            if ($endOfTokens === false) {
                --$l;
                $lookAheadToken = $this->tokens[$l];
            }

            // Check if a rule matches and reduce.
            for ($i = $l - 1; $i >= 0; --$i) {
                // Construct the key.
                $key = "";
                for ($j = $i; $j <= $l; ++$j) {
                    $key .= $this->tokens[$j]->type->key;
                }

                $rule = $this->rules->get($key);

                // Check if we have a rule for key.
                if ($rule === null) {
                    continue;
                }

                // Check precedence of look ahead token
                if ($lookAheadToken->type->precendence > $rule->precedence) {
                    continue;
                }

                // Call the rule's reduce function.
                $newToken = $rule->reduce(...array_slice($this->tokens[$i]));

                // Replace matched tokens with new token
                array_splice($this->tokens, $i, $l - $i, [$newToken]);

                // Check for more rules with new state.
                continue 2;
            }
            return;
        }
    }
}
