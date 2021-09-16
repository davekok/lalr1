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
class Parser implements ParserInterface
{
    /**
     * If a solution has been found, this property contains the value of the root token.
     */
    private mixed $solution;
    private array $debugLog = [];
    private array $tokens = [];
    private int $pushCounter = 0;

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
            $debugLog = "createToken($name, ".var_export($value, true)."): ";
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

        if ($token->symbol instanceof BranchSymbol === true) {
            if ($this->debug) {
                $this->debugLog[] = $debugLog . ": You should not push branch symbols.";
            }

            throw new ParserException([$token], ": You should not push branch symbols.");
        }

        $this->tokens[] = $token;

        if ($this->debug) {
            $this->debugLog[] = $debugLog;
        }

        $this->reduce(false);

        ++$this->pushCounter;
    }

    /**
     * Signal end of tokens has been reached and return solution.
     */
    public function endOfTokens(): mixed
    {
        if ($this->debug) {
            $this->debugLog[] = "endOfTokens:: length: " . count($this->tokens);
        }

        if (count($this->tokens) === 0) {
            return null;
        }

        $this->reduce(true);

        if (count($this->tokens) !== 1) {
            if ($this->debug) {
                $this->debugLog[] = "endOfTokens: token count is not 1 but " . count($this->tokens);
            }

            throw new NoSolutionParserException();
        }

        $token = array_pop($this->tokens);

        // If token has the root symbol, we have a solution.
        if ($token->symbol !== $this->symbols->rootSymbol) {
            if ($this->debug) {
                $this->debugLog[] = "endOfTokens: token is not root";
            }

            throw new NoSolutionParserException();
        }

        if ($this->debug) {
            $this->debugLog[] = "endOfTokens: solution: ". $token;
        }

        return $token->value;
    }

    public function getDebugLog(): array
    {
        return $this->debugLog;
    }

    private function reduce(bool $endOfTokens): void
    {
        for (;;) {
            if ($this->debug) {
                $debugLog = "reduce";
            }

            $length = count($this->tokens);

            // Skip the lookahead token until the end has been reached.
            if ($endOfTokens === false) {
                --$length;
                $lookAheadToken = $this->tokens[$length];
            } else {
                $lookAheadToken = null;
            }

            // Check if a rule matches and reduce.
            for ($offset = 0; $offset < $length; ++$offset) {
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
                    $this->debugLog[] = $debugLog . ": by rule " . bin2hex($rule->key);
                }

                // Check for more rules with new state.
                continue 2;
            }

            if ($this->debug) {
                $this->debugLog[] = $debugLog . ": length: " . count($this->tokens);
            }

            return;
        }
    }
}
