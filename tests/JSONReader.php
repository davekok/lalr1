<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\stream\{ReaderBuffer,Reader,ReaderException};
use davekok\lalr1\Token;
use davekok\lalr1\Parser;

enum JSONState {
    case YYSTART;
    case YYSTRING;
    case YYESCAPE;
    case YYCODEPOINT;
    case YYNUMBER;
    case YYMINUS;
    case YYZERO;
    case YYFRACTION;
    case YYEXPONENT_SIGN;
    case YYEXPONENT;
    case YYTRUE_T;
    case YYTRUE_R;
    case YYTRUE_U;
    case YYTRUE_E;
    case YYFALSE_F;
    case YYFALSE_A;
    case YYFALSE_L;
    case YYFALSE_S;
    case YYFALSE_E;
    case YYNULL_N;
    case YYNULL_U;
    case YYNULL_L;
    case YYNULL_LL;
}

class JSONReader implements Reader
{
    private JSONState $state = JSONState::YYSTART;
    private string $string;
    private int $codePoint;
    private int $codePointCount;
    private bool $negative;
    private bool $negativeExponent;
    private int $number;
    private float $fraction;

    public function __construct(
        private readonly Parser $parser
    ) {}

    public function reset(): void
    {
        $this->state = JSONState::YYSTART;
    }

    public function endOfInput(ReaderBuffer $buffer): void
    {
        $this->parser->endOfTokens();
    }

    public function read(ReaderBuffer $buffer): void
    {
        while ($buffer->valid()) {
            switch ($this->state) {
                case JSONState::YYSTART:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x00:case 0x01:case 0x02:case 0x03:case 0x04:case 0x05:case 0x06:case 0x07:
                        case 0x08:                    case 0x0B:case 0x0C:          case 0x0E:case 0x0F:
                        case 0x10:case 0x11:case 0x12:case 0x13:case 0x14:case 0x15:case 0x16:case 0x17:
                        case 0x18:case 0x19:case 0x1A:case 0x0B:case 0x0C:case 0x1D:case 0x0E:case 0x0F:
                        case 0x7F: // control characters
                            throw new ReaderException("Control characters not allowed.");
                        case 0x09:case 0x0A:case 0x0D:case 0x20: // skip space characters
                            $buffer->next();
                            continue 3;
                        case 0x22: // start string
                            $buffer->next()->mark(); // skip quote
                            $this->state  = JSONState::YYSTRING;
                            $this->string = "";
                            continue 3;
                        case 0x2D: // minus
                            $buffer->next()->mark();
                            $this->negative = true;
                            $this->number   = 0;
                            $this->state    = JSONState::YYMINUS;
                            continue 3;
                        case 0x2C: // comma
                            $buffer->mark()->next();
                            $this->parser->pushToken(",");
                            continue 3;
                        case 0x30:
                            $buffer->mark()->next();
                            $this->negative = false;
                            $this->number = 0;
                            $this->state  = JSONState::YYZERO;
                            continue 3;
                        case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $buffer->mark()->next();
                            $this->negative = false;
                            $this->number   = $c - 0x30;
                            $this->state    = JSONState::YYNUMBER;
                            continue 3;
                        case 0x3A: // colon
                            $buffer->mark()->next();
                            $this->parser->pushToken(":");
                            continue 3;
                        case 0x5B: // opening bracket
                            $buffer->mark()->next();
                            $this->parser->pushToken("[");
                            continue 3;
                        case 0x5D: // closing bracket
                            $buffer->mark()->next();
                            $this->parser->pushToken("]");
                            continue 3;
                        case 0x66: // f
                            $buffer->mark()->next();
                            $this->state = JSONState::YYFALSE_A;
                            continue 3;
                        case 0x6E: // n
                            $buffer->mark()->next();
                            $this->state = JSONState::YYNULL_U;
                            continue 3;
                        case 0x74: // t
                            $buffer->mark()->next();
                            $this->state = JSONState::YYTRUE_R;
                            continue 3;
                        case 0x7B: // opening brace
                            $buffer->mark()->next();
                            $this->parser->pushToken("{");
                            continue 3;
                        case 0x7D: // closing brace
                            $buffer->mark()->next();
                            $this->parser->pushToken("}");
                            continue 3;
                        default:
                            throw new ReaderException("Non ASCII characters not support outside string.");
                    }
                case JSONState::YYSTRING:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x00:case 0x01:case 0x02:case 0x03:case 0x04:case 0x05:case 0x06:case 0x07:
                        case 0x08:                    case 0x0B:case 0x0C:          case 0x0E:case 0x0F:
                        case 0x10:case 0x11:case 0x12:case 0x13:case 0x14:case 0x15:case 0x16:case 0x17:
                        case 0x18:case 0x19:case 0x1A:case 0x0B:case 0x0C:case 0x1D:case 0x0E:case 0x0F:
                        case 0x7F: // control characters
                            throw new ReaderException("Control characters not allowed.");
                        case 0x22:
                            $this->string .= $this->getString();
                            $this->parser->pushToken("string", $this->string);
                            $buffer->next()->mark();
                            continue 3;
                        case 0x5C:
                            $this->string .= $this->getString();
                            $buffer->next()->mark();
                            $this->state = JSONState::YYESCAPE;
                            continue 3;
                        default:
                            if ($c >= 0x80) {
                                // TODO: validate UTF-8 sequence, is UTF-8 and not over long.
                            }
                    }
                case JSONState::YYESCAPE:
                    switch ($buffer->peek()) {
                        case 0x22:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "\"";
                            continue 3;
                        case 0x2F:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "/";
                            continue 3;
                        case 0x62:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "\x07";
                            continue 3;
                        case 0x66:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "\x0C";
                            continue 3;
                        case 0x6E:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "\n";
                            continue 3;
                        case 0x72:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "\r";
                            continue 3;
                        case 0x74:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "\t";
                            continue 3;
                        case 0x75:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYCODEPOINT;
                            $this->codePoint = 0;
                            $this->codePointCount = 0;
                            continue 3;
                        case 0x5C:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTRING;
                            $this->string .= "\\";
                            continue 3;
                        default:
                            throw new ReaderException("Invalid escape sequence.");
                    }
                case JSONState::YYCODEPOINT:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:
                        case 0x38:case 0x39:
                            $this->codePoint <<= 4;
                            $this->codePoint |= $c - 0x30;
                            break;
                        case 0x41:case 0x42:case 0x43:case 0x44:case 0x45:case 0x46:
                            $this->codePoint <<= 4;
                            $this->codePoint |= $c - 0x37;
                            break;
                        case 0x61:case 0x62:case 0x63:case 0x64:case 0x65:case 0x66:
                            $this->codePoint <<= 4;
                            $this->codePoint |= $c - 0x57;
                            break;
                        default:
                            throw new ReaderException("Invalid escape sequence.");
                    }
                    if (++$this->codePointCount == 4) {
                        $this->state = JSONState::YYSTRING;
                        $this->string .= self::utf8($this->codePoint);
                    }
                    continue 2;
                case JSONState::YYTRUE_R:
                    switch ($buffer->peek()) {
                        case 0x72:
                            $buffer->next();
                            $this->state = JSONState::YYTRUE_U;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYTRUE_U:
                    switch ($buffer->peek()) {
                        case 0x75:
                            $buffer->next();
                            $this->state = JSONState::YYTRUE_E;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYTRUE_E:
                    switch ($buffer->peek()) {
                        case 0x65:
                            $buffer->next();
                            $this->state = JSONState::YYSTART;
                            $this->parser->pushToken("boolean", true);
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYFALSE_A:
                    switch ($buffer->peek()) {
                        case 0x61:
                            $buffer->next();
                            $this->state = JSONState::YYFALSE_L;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYFALSE_L:
                    switch ($buffer->peek()) {
                        case 0x6C:
                            $buffer->next();
                            $this->state = JSONState::YYFALSE_S;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYFALSE_S:
                    switch ($buffer->peek()) {
                        case 0x73:
                            $buffer->next();
                            $this->state = JSONState::YYFALSE_E;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYFALSE_E:
                    switch ($buffer->peek()) {
                        case 0x65:
                            $buffer->next();
                            $this->state = JSONState::YYSTART;
                            $this->parser->pushToken("boolean", false);
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYNULL_U:
                    switch ($buffer->peek()) {
                        case 0x6E:
                            $buffer->next();
                            $this->state = JSONState::YYNULL_L;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYNULL_L:
                    switch ($buffer->peek()) {
                        case 0x6C:
                            $buffer->next();
                            $this->state = JSONState::YYNULL_LL;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYNULL_LL:
                    switch ($buffer->peek()) {
                        case 0x6C:
                            $buffer->next();
                            $this->state = JSONState::YYSTART;
                            $this->parser->pushToken("null");
                            continue 3;
                        default:
                            throw new ReaderException("Invalid key word");
                    }
                case JSONState::YYMINUS:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x30:
                            $buffer->mark()->next();
                            $this->state = JSONState::YYZERO;
                            continue 3;
                        case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                            $buffer->mark()->next();
                            $this->number += $c - 0x30;
                            $this->state = JSONState::YYNUMBER;
                            continue 3;
                        default:
                            throw new ReaderException("Stray minus sign.");
                    }
                case JSONState::YYZERO:
                    switch ($buffer->peek()) {
                        case 0x2E:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYFRACTION;
                            continue 3;
                        default:
                            $this->parser->pushToken("number", 0);
                            $buffer->next()->mark();
                            $this->state = JSONState::YYSTART;
                            continue 3;
                    }
                case JSONState::YYNUMBER:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:
                        case 0x38:case 0x39:
                            $buffer->next()->mark();
                            $this->number *= 10;
                            $this->number += $c - 0x30;
                            continue 3;
                        case 0x2E: // dot
                            $buffer->next()->mark();
                            $this->fraction = $this->number;
                            $this->number   = 10;
                            $this->state    = JSONState::YYFRACTION;
                            continue 3;
                        case 0x45:case 0x65:
                            $buffer->next()->mark();
                            $this->fraction = $this->number;
                            $this->state = JSONState::YYEXPONENT_SIGN;
                            continue 3;
                        default:
                            $buffer->next()->mark();
                            if ($this->negative) {
                                $this->number = -$this->number;
                            }
                            $this->parser->pushToken("number", $this->number);
                            $this->state = JSONState::YYSTART;
                            continue 3;
                    }
                case JSONState::YYFRACTION:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:
                        case 0x38:case 0x39:
                            $buffer->next()->mark();
                            $this->fraction += ($this->fraction - 0x30) / $this->number;
                            $this->number *= 10;
                            continue 3;
                        case 0x45:case 0x65:
                            $buffer->next()->mark();
                            $this->state = JSONState::YYEXPONENT_SIGN;
                            continue 3;
                        default:
                            $this->parser->pushToken("number", $this->number);
                            $this->state = JSONState::YYSTART;
                            continue 3;
                    }
                case JSONState::YYEXPONENT_SIGN:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:
                        case 0x38:case 0x39:
                            $buffer->next()->mark();
                            $this->negativeExponent = false;
                            $this->number = $c - 0x30;
                            $this->state = JSONState::YYEXPONENT;
                            continue 3;
                        case 0x2B: // minus
                            $buffer->next()->mark();
                            $this->negativeExponent = true;
                            $this->number = 0;
                            $this->state = JSONState::YYEXPONENT;
                            continue 3;
                        case 0x2D: // plus
                            $buffer->next()->mark();
                            $this->negativeExponent = false;
                            $this->number = 0;
                            $this->state = JSONState::YYEXPONENT;
                            continue 3;
                        default:
                            throw new ReaderException("Invalid exponent");
                    }
                case JSONState::YYEXPONENT:
                    $c = $buffer->peek();
                    switch ($c) {
                        case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:
                        case 0x38:case 0x39:
                            $buffer->next()->mark();
                            $this->number += $c - 0x30;
                            continue 3;
                        default:
                            if ($this->negativeExponent) {
                                $this->fraction /= 10 ** $this->number;
                            } else {
                                $this->fraction *= 10 ** $this->number;
                            }
                            $this->parser->pushToken("number", $this->fraction);
                            $this->state = JSONState::YYSTART;
                            continue 3;
                    }
            }
        }
    }

    private static function utf8(int $codePoint): string
    {
        if ($codePoint <= 0x007F) {
            return chr($codePoint);
        }

        if ($codePoint <= 0x07FF) {
            return chr(($codePoint >> 6) | 0b11000000)
                .  chr($codePoint & 0b00111111 | 0b10000000);
        }

        if ($codePoint <= 0xFFFF) {
            return chr(($codePoint >> 12) | 0b11100000)
                .  chr(($codePoint >> 6) & 0b00111111 | 0b10000000)
                .  chr($codePoint & 0b00111111 | 0b10000000);
        }

        if ($codePoint <= 0x10FFFF) {
            return chr(($codePoint >> 18) | 0b11110000)
                .  chr(($codePoint >> 12) & 0b00111111 | 0b10000000)
                .  chr(($codePoint >> 6) & 0b00111111 | 0b10000000)
                .  chr($codePoint & 0b00111111 | 0b10000000);
        }

        throw new ReaderException("Code points larger then 10FFFF are not supported.");
    }
}
