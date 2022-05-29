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
 * When there are no more tokens push, endOfTokens is called.
 *
 * A solution is found when at the end of tokens, through reducing, only one token remains having
 * the root symbol and thus being a root token.
 */
trait ParserTrait/*<EnumType>*/
{
    /*
    abstract private function lex(iterable $input): EnumType;
    abstract private function findRule(string $key): EnumType;
    abstract private function reduce(EnumType $rule, array $tokens): Token;
    */

    public function parseTokens(iterable $inputTokens): Generator
    {
        $tokens = new Tokens;
        foreach ($inputTokens as $token) {
            if ($token !== null) {
                if ($token->type->input() === false) {
                    throw new ParserException("Not a input type.");
                }
                $tokens->push($token);
                $this->scanTokens($tokens, endOfTokens: false);
                continue;
            }

            if ($tokens->count() === 0) {
                yield null;
                continue;
            }

            $this->scanTokens($tokens, endOfTokens: true);

            if ($tokens->count() != 1) {
                throw new NoSolutionParserException("Token count is not 1 but {$tokens->count()}");
            }

            $token = $tokens->pop();

            // If token has the root symbol, we have a solution.
            if ($token->type->output() === false) {
                throw new NoSolutionParserException("Token is not root.");
            }

            yield $token->value;
        }
    }

    private function scanTokens(Tokens $tokens, bool $endOfTokens): void
    {
        for (;;) {
            $count = $tokens->count();

            // Skip the lookahead token until the end has been reached.
            $lookAheadToken = null;
            if ($endOfTokens === false) {
                --$count;
                $lookAheadToken = $tokens->last();
            }

            // Check if a rule matches and reduce.
            for ($offset = 0; $offset < $count; ++$offset) {
                // Construct the key.
                $key = "";
                for ($o = $offset; $o < $count; ++$o) {
                    $key .= $tokens->get($o)->type->key();
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
                $newToken = $this->reduce($rule, $tokens->rangeFrom($offset));

                // Replace matched tokens with new token
                $tokens->replace($offset, $count - $offset, $newToken);

                // Check for more rules with new state.
                continue 2;
            }

            return;
        }
    }
}
