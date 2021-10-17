<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\{Parser,Token};
use davekok\stream\{ScanBuffer,Scanner,ScanException};
use Exception;

enum ExpressionState
{
    case YYSTART;
    case YYINT;
    case YYFLOAT;
}

class ExpressionScanner implements Scanner
{
    private ExpressionState $state = ExpressionState::YYSTART;

    public function __construct(
        private readonly Parser $parser,
        private readonly ScanBuffer $buffer = new ScanBuffer()
    ) {}

    public function reset(): void
    {
        $this->buffer->reset();
        $this->state = ExpressionState::YYSTART;
        $this->parser->reset();
    }

    public function endOfInput(): void
    {
        switch ($this->state) {
            case ExpressionState::YYINT:
                $this->parser->pushToken("number", $this->buffer->getInt());
                break;
            case ExpressionState::YYFLOAT:
                $this->parser->pushToken("number", $this->buffer->getFloat());
                break;
        }
        $this->parser->endOfTokens();
    }

    public function scan(string $input): void
    {
        $this->buffer->add($input);
        while ($this->buffer->valid()) {
            switch ($this->state) {
                case ExpressionState::YYSTART:
                    switch ($this->buffer->peek()) {
                        case 0x09:case 0x0A:case 0x0D:case 0x20:
                            $this->buffer->mark()->next();
                            continue 3;
                        case 0x28:
                            $this->buffer->mark()->next();
                            $this->parser->pushToken("(");
                            continue 3;
                        case 0x29:
                            $this->buffer->mark()->next();
                            $this->parser->pushToken(")");
                            continue 3;
                        case 0x2A:
                            $this->buffer->mark()->next();
                            $this->parser->pushToken("*");
                            continue 3;
                        case 0x2B:
                            $this->buffer->mark()->next();
                            $this->parser->pushToken("+");
                            continue 3;
                        case 0x2D:
                            $this->buffer->mark()->next();
                            $this->parser->pushToken("-");
                            continue 3;
                        case 0x2F:
                            $this->buffer->mark()->next();
                            $this->parser->pushToken("/");
                            continue 3;
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $this->buffer->mark()->next();
                            $this->state = ExpressionState::YYINT;
                            continue 3;
                        case 0x5C:
                            $this->buffer->mark()->next();
                            $this->parser->pushToken("\\");
                            continue 3;
                        default:
                            throw new ScanException("Scan error");
                    }
                case ExpressionState::YYINT:
                    switch ($this->buffer->peek()) {
                        case 0x2E:
                            $this->buffer->next();
                            $this->state = ExpressionState::YYFLOAT;
                            continue 3;
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $this->buffer->next();
                            continue 3;
                        default:
                            $this->parser->pushToken("number", $this->buffer->getInt());
                            $this->state = ExpressionState::YYSTART;
                            continue 3;
                    }
                case ExpressionState::YYFLOAT:
                    switch ($this->buffer->peek()) {
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $this->buffer->next();
                            continue 3;
                        default:
                            $this->parser->pushToken("number", $this->buffer->getFloat());
                            $this->state = ExpressionState::YYSTART;
                            continue 3;
                    }
            }
        }
    }
}
