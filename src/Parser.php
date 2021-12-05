<?php

declare(strict_types=1);

namespace davekok\lalr1;

/**
 * This class implements the Look Ahead, Left to right, Right most derivation algorithm.
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
class Parser
{
    public function __construct(
        private readonly RulesBag $rulesBag,
        private readonly Rules    $rules,
        private readonly Tokens   $tokens = new Tokens,
    ) {
        $this->rules->setParser($this);
    }

    public function reset(): void
    {
        $this->tokens->reset();
    }

    public function createToken(string $name, mixed $value): Token
    {
        $symbol = $this->rulesBag->getSymbol($name);
        if ($symbol == null) {
            throw new ParserException("No such symbol '$name'.");
        }
        return new Token($symbol, $value);
    }

    public function pushToken(string $name, mixed $value = null): void
    {
        $token = $this->createToken($name, $value);
        if ($token->symbol->type === SymbolType::BRANCH) {
            throw new ParserException("You should not push branch symbols.");
        }
        $this->tokens->push($token);
        $this->reduce(false);
    }

    public function endOfTokens(): mixed
    {
        if ($this->tokens->count() == 0) {
            return null;
        }

        $this->reduce(true);

        if ($this->tokens->count() != 1) {
            return new NoSolutionParserException("Token count is not 1 but {$this->tokens->count()}");
        }

        $token = $this->tokens->pop();

        // If token has the root symbol, we have a solution.
        if ($token->symbol->type != SymbolType::ROOT) {
            return new NoSolutionParserException("Token is not root.");
        }

        return $token->value;
    }

    private function reduce(bool $endOfTokens): void
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
                    $key .= $this->tokens->get($o)->symbol->key;
                }

                $rule = $this->rulesBag->getRule($key);

                // Check if we have a rule for key.
                if ($rule == null) {
                    continue;
                }

                // Check precedence of look ahead token
                if ($lookAheadToken != null && $lookAheadToken->symbol->precedence > $rule->precedence) {
                    continue;
                }

                // Call the rule's reduce function.
                $newToken = $rule->reduce($this->rules, $this->tokens->rangeFrom($offset));

                // Replace matched tokens with new token
                $this->tokens->replace($offset, $count - $offset, $newToken);

                // Check for more rules with new state.
                continue 2;
            }

            return;
        }
    }
}
