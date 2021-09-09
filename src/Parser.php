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
 * is matched, the rule's reduce function is called and the matched tokens are replaced with the
 * token returned by the reduce function.
 *
 * When there are no more tokens push, endOfTokens is called.
 *
 * A solution is found when at the end of tokens, through reducing, only one token remains having
 * the root symbol and thus being the root token.
 *
 * An implementing class has set the Symbols attribute on the
 * class and the Rule attribute on methods implementing a rule.
 *
 * Example:
 *
 *     use DaveKok\LALR1\Symbols;
 *     use DaveKok\LALR1\RootSymbol;
 *     use DaveKok\LALR1\BranchSymbol;
 *     use DaveKok\LALR1\LeafSymbol;
 *     use DaveKok\LALR1\Parser;
 *     use DaveKok\LALR1\ParserFactory;
 *
 *     #[Symbols(
 *         new RootSymbol("my-root-symbol"),
 *         new BranchSymbol("my-branch-symbol"),
 *         new LeafSymbol("my-leaf-symbol")
 *     )]
 *     class MyParser
 *     {
 *         private readonly Parser $parser;
 *
 *         public function __construct()
 *         {
 *              $this->parser = ParserFactory::createParser($this);
 *         }
 *
 *         public function parse(): string
 *         {
 *              $this->parser->pushToken($this->parser->createToken("my-leaf-symbol", "my value"));
 *              $this->parser->endOfTokens();
 *              return $this->parser->value;
 *         }
 *
 *         #[Rule("my-leaf-symbol")]
 *         public function promoteLeafToken(Token $myLeafToken): Token
 *         {
 *              return $this->parser->createToken("my-branch-symbol", $myLeafToken->value);
 *         }
 *
 *         #[Rule("my-branch-symbol")]
 *         public function promoteBranchToken(Token $myBranchToken): Token
 *         {
 *              return $this->parser->createToken("my-root-symbol", $myBranchToken->value);
 *         }
 *     }
 */
class Parser
{
    /**
     * If a solution has been found, this property contains the value of the root token.
     */
    public mixed $value;

    private array $tokens = [];

    public function __construct(
        private readonly Symbols $symbols,
        public readonly Rules $rules
    ) {}

    /**
     * Create a new token.
     */
    public function createToken(string $name, mixed $value = null): Token
    {
        $type = $this->symbols->getByName($name) ?? null;
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
        if ($token->symbol instanceof LeafSymbol === false) {
            throw new LogicException("You should only push leaf symbols.");
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

                $rootToken = array_pop($this->tokens);

                // The root token should have the root symbol.
                if ($rootToken->symbol !== $this->symbols->rootSymbol) {
                    throw new LogicException("End of tokens reached, but no valid solution.");
                }

                $this->value = $rootToken->value;

                return;
            }

            // Skip the lookahead token until the end has been reached.
            if ($endOfTokens === false) {
                --$l;
                $lookAheadToken = $this->tokens[$l];
            } else {
                $lookAheadToken = null;
            }

            // Check if a rule matches and reduce.
            for ($i = $l - 1; $i >= 0; --$i) {
                // Construct the key.
                $key = "";
                for ($j = $i; $j < $l; ++$j) {
                    $key .= $this->tokens[$j]->symbol->key;
                }

                $rule = $this->rules->get($key);

                // Check if we have a rule for key.
                if ($rule === null) {
                    continue;
                }

                // Check precedence of look ahead token
                if ($lookAheadToken !== null
                        && $lookAheadToken->symbol->precedence > $rule->precedence)
                {
                    continue;
                }

                // Call the rule's reduce function.
                $newToken = ($rule->reduce)(...array_slice($this->tokens, $i));

                // Replace matched tokens with new token
                array_splice($this->tokens, $i, $l - $i, [$newToken]);

                // Check for more rules with new state.
                continue 2;
            }
            return;
        }
    }
}
