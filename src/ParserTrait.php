<?php

declare(strict_types=1);

namespace davekok\parser;

use Generator;

/**
 * This trait implements the Look Ahead, Left to right, Right most derivation algorithm.
 *
 * It works by pushing tokens. Then the right most (last pushed) token(s) are checked against
 * the rules, excluding the last pushed token as it is used as the look ahead token. If a rule
 * is matched, the rule's reduce method is called and the matched tokens are replaced with the
 * token returned by the reduce method.
 *
 * When there are no more tokens push, push null to get a solution.
 *
 * A solution is found when at the end of tokens, through reducing, only one token remains and
 * it is an output token.
 *
 * Please note that you can only push input tokens.
 */
trait ParserTrait
{
    private Tokens $tokens;

    abstract private function findRule(string $key): RuleEnum|null;
    abstract private function reduce(RuleEnum $rule, array $tokens): Token;

    /**
     * Simple wrapper function around pushToken.
     */
    private function parseTokens(iterable $inputTokens): Generator
    {
        foreach ($inputTokens as $inputToken) {
            $outputToken = $this->pushToken($inputToken);

            if ($outputToken === false) {
                continue;
            }

            if ($outputToken === null) {
                yield null;
                continue;
            }

            yield $outputToken;
        }
    }

    private function pushToken(Token|null $inputToken): Token|false|null
    {
        $this->tokens ??= new Tokens;

        if ($inputToken !== null) {
            if ($inputToken->type->input() === false) {
                throw new ParserException("Not a input token.");
            }
            $this->tokens->push($inputToken);
            $this->scanTokens(endOfTokens: false);
            return false;
        }

        if ($this->tokens->count() === 0) {
            return null;
        }

        $this->scanTokens(endOfTokens: true);

        if ($this->tokens->count() != 1) {
            throw new NoSolutionParserException("Token count is not one but {$this->tokens->count()}");
        }

        $outputToken = $this->tokens->pop();

        // If token is an output token, we have a solution.
        if ($outputToken->type->output() === false) {
            throw new NoSolutionParserException("Remaining token is not an output token.");
        }

        return $outputToken;
    }

    private function scanTokens(bool $endOfTokens): void
    {
        for (;;) {
            $count = $this->tokens->count();

            // Skip the lookahead token until the end has been reached.
            $lookAheadToken = null;
            if ($endOfTokens === false) {
                --$count;
                $lookAheadToken = $this->tokens->last();
            }

            // Check if a rule matches and reduce.
            for ($offset = 0; $offset < $count; ++$offset) {
                // Construct the key.
                $key = "";
                for ($o = $offset; $o < $count; ++$o) {
                    $key .= $this->tokens->get($o)->type->key();
                }

                $rule = $this->findRule($key);

                // Check if we have a rule for key.
                if ($rule == null) {
                    continue;
                }

                // Check precedence of look ahead token
                if ($lookAheadToken != null && $lookAheadToken->type->precedence() > $rule->precedence()) {
                    continue;
                }

                // Reduce tokens.
                $newToken = $this->reduce($rule, $this->tokens->rangeFrom($offset));

                // Replace matched tokens with new token
                $this->tokens->replace($offset, $count - $offset, $newToken);

                // Check for more rules with new state.
                continue 2;
            }

            return;
        }
    }
}
