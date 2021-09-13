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
    private array $debugLog = [];

    public function __construct(
        private readonly Symbols $symbols,
        private readonly Rules $rules,
        private readonly bool $debug = false
    ) {}

    /**
     * Create a new token.
     */
    public function createToken(string $name, mixed $value = null): Token
    {
        if ($this->debug) {
            $debugLog = "createToken($name, $value): ";
        }
        $type = $this->symbols->getByName($name) ?? null;
        if ($type === null) {
            if ($this->debug) {
                $this->debugLog[] = $debugLog . "No such type";
            }
            throw new ParserException([], "No such type '$name'.");
        }

        $token = new Token($type, $value);

        if ($this->debug) {
            $this->debugLog[] = $debugLog . $token;
        }

        return $token;
    }

    /**
     * Push a token.
     */
    public function pushToken(Token $token): void
    {
        if ($this->debug) {
            $debugLog = "pushToken($token)";
        }
        if ($token->symbol instanceof LeafSymbol === false) {
            if ($this->debug) {
                $this->debugLog[] = $debugLog . ": You should only push leaf symbols.";
            }
            throw new ParserException([$token], ": You should only push leaf symbols.");
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
            if ($this->debug) {
                $debugLog = "reduce(".($endOfTokens?"endOfTokens":"").")";
            }

            $length = count($this->tokens);

            if ($length === 0) {
                // Edge case: In case the first push is null.
                if ($endOfTokens === true) {
                    if ($this->debug) {
                        $this->debugLog[] = $debugLog . ": Edge case, first push is null";
                    }

                    throw new NoSolutionParserException();
                }

                if ($this->debug) {
                    $this->debugLog[] = $debugLog . ": Invalid internal state.";
                }

                throw new ParserException("Invalid internal state.");
            }

            // Skip the lookahead token until the end has been reached.
            if ($endOfTokens === false) {
                --$length;
                $lookAheadToken = $this->tokens[$length];
            } else {
                $lookAheadToken = null;
            }

            // Check if a rule matches and reduce.
            for ($offset = $length - 1; $offset >= 0; --$offset) {
                // Construct the key.
                $key = "";
                for ($o = $offset; $o < $length; ++$o) {
                    $key .= $this->tokens[$o]->symbol->key;
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
                $newToken = ($rule->reduce)(...array_slice($this->tokens, $offset));

                // Replace matched tokens with new token
                array_splice($this->tokens, $offset, $length - $offset, [$newToken]);

                if ($this->debug) {
                    $this->debugLog[] = $debugLog . ": reduced by " . bin2hex($rule->key);
                }

                // Check for more rules with new state.
                continue 2;
            }

            if ($length === 1) {
                // Edge case: first push should be ignored as it only arms the look ahead.
                if ($endOfTokens === false) {
                    if ($this->debug) {
                        $this->debugLog[] = $debugLog . ": edge case: first push";
                    }
                    return;
                }

                $token = array_pop($this->tokens);

                // If token has the root symbol, we have a solution.
                if ($token->symbol !== $this->symbols->rootSymbol) {
                    if ($this->debug) {
                        $this->debugLog[] = $debugLog . ": no solution found";
                    }
                    throw new NoSolutionParserException();
                }

                $this->value = $token->value;

                if ($this->debug) {
                    $this->debugLog[] = $debugLog . ": solution: ". $token;
                }

                return;
            }

            if ($endOfTokens === true) {
                if ($this->debug) {
                    $this->debugLog[] = $debugLog . ": no solution found";
                }
                throw new NoSolutionParserException();
            }

            return;
        }
    }
}
