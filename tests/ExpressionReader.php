<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\{Parser,Token};
use davekok\stream\{ReaderBuffer,ReaderException};
use Exception;

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

    public function reset(): void
    {
        $this->state = ExpressionState::YYSTART;
        $this->parser->reset();
    }

    public function read(ReaderBuffer $buffer): void
    {
        while ($buffer->valid()) {
            switch ($this->state) {
                case ExpressionState::YYSTART:
                    switch ($buffer->peek()) {
                        case 0x09:case 0x0A:case 0x0D:case 0x20:
                            $buffer->mark()->next();
                            continue 3;
                        case 0x28:
                            $buffer->mark()->next();
                            $this->parser->pushToken("(");
                            continue 3;
                        case 0x29:
                            $buffer->mark()->next();
                            $this->parser->pushToken(")");
                            continue 3;
                        case 0x2A:
                            $buffer->mark()->next();
                            $this->parser->pushToken("*");
                            continue 3;
                        case 0x2B:
                            $buffer->mark()->next();
                            $this->parser->pushToken("+");
                            continue 3;
                        case 0x2D:
                            $buffer->mark()->next();
                            $this->parser->pushToken("-");
                            continue 3;
                        case 0x2F:
                            $buffer->mark()->next();
                            $this->parser->pushToken("/");
                            continue 3;
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $buffer->mark()->next();
                            $this->state = ExpressionState::YYINT;
                            continue 3;
                        case 0x5C:
                            $buffer->mark()->next();
                            $this->parser->pushToken("\\");
                            continue 3;
                        default:
                            throw new ReaderException("Scan error");
                    }
                case ExpressionState::YYINT:
                    switch ($buffer->peek()) {
                        case 0x2E:
                            $buffer->next();
                            $this->state = ExpressionState::YYFLOAT;
                            continue 3;
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $buffer->next();
                            continue 3;
                        default:
                            $this->parser->pushToken("number", $buffer->getInt());
                            $this->state = ExpressionState::YYSTART;
                            continue 3;
                    }
                case ExpressionState::YYFLOAT:
                    switch ($buffer->peek()) {
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $buffer->next();
                            continue 3;
                        default:
                            $this->parser->pushToken("number", $buffer->getFloat());
                            $this->state = ExpressionState::YYSTART;
                            continue 3;
                    }
            }
        }
        if ($buffer->isLastChunk() === true) {
            switch ($this->state) {
                case ExpressionState::YYINT:
                    $this->parser->pushToken("number", $buffer->getInt());
                    break;
                case ExpressionState::YYFLOAT:
                    $this->parser->pushToken("number", $buffer->getFloat());
                    break;
            }
            $this->parser->endOfTokens();
            return;
        }
    }
}
