<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\{Parser,Token};
use Exception;
use Throwable;

enum ExpressionState
{
    case YYSTART;
    case YYINT;
    case YYFLOAT;
}

class ExpressionReader
{
    private ExpressionState $state = ExpressionState::YYSTART;

    public function __construct(
        private readonly Parser $parser
    ) {}

    public function read(string $buffer): int|float
    {
        try {
            $mark   = 0;
            $offset = 0;
            while ($offset < strlen($buffer)) {
                switch ($this->state) {
                    case ExpressionState::YYSTART:
                        switch (ord($buffer[$offset])) {
                            case 0x09:case 0x0A:case 0x0D:case 0x20:
                                $mark = $offset++;
                                continue 3;
                            case 0x28:
                                $mark = $offset++;
                                $this->parser->pushToken("(");
                                continue 3;
                            case 0x29:
                                $mark = $offset++;
                                $this->parser->pushToken(")");
                                continue 3;
                            case 0x2A:
                                $mark = $offset++;
                                $this->parser->pushToken("*");
                                continue 3;
                            case 0x2B:
                                $mark = $offset++;
                                $this->parser->pushToken("+");
                                continue 3;
                            case 0x2D:
                                $mark = $offset++;
                                $this->parser->pushToken("-");
                                continue 3;
                            case 0x2F:
                                $mark = $offset++;
                                $this->parser->pushToken("/");
                                continue 3;
                            case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                                $mark = $offset++;
                                $this->state = ExpressionState::YYINT;
                                continue 3;
                            case 0x5C:
                                $mark = $offset++;
                                $this->parser->pushToken("\\");
                                continue 3;
                            default:
                                throw new Exception("Scan error");
                        }
                    case ExpressionState::YYINT:
                        switch (ord($buffer[$offset])) {
                            case 0x2E:
                                ++$offset;
                                $this->state = ExpressionState::YYFLOAT;
                                continue 3;
                            case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                                ++$offset;
                                continue 3;
                            default:
                                $this->parser->pushToken("number", (int)substr($buffer, $mark, $offset));
                                $this->state = ExpressionState::YYSTART;
                                continue 3;
                        }
                    case ExpressionState::YYFLOAT:
                        switch (ord($buffer[$offset])) {
                            case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                                ++$offset;
                                continue 3;
                            default:
                                $this->parser->pushToken("number", (float)substr($buffer, $mark, $offset));
                                $this->state = ExpressionState::YYSTART;
                                continue 3;
                        }
                }
            }
            switch ($this->state) {
                case ExpressionState::YYINT:
                    $this->parser->pushToken("number", (int)substr($buffer, $mark, $offset));
                    break;
                case ExpressionState::YYFLOAT:
                    $this->parser->pushToken("number", (float)substr($buffer, $mark, $offset));
                    break;
            }
            return $this->parser->endOfTokens();
        } catch (Throwable $e) {
            $this->state = ExpressionState::YYSTART;
            $this->parser->reset();
            throw $e;
        }
    }
}
