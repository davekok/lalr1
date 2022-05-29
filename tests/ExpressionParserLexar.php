<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use Exception;
use Generator;
use Throwable;

enum ExpressionParserLexarState
{
    case YYSTART;
    case YYINT;
    case YYFLOAT;
}

/**
 * Lexal analyzer for ExpressionParser
 */
trait ExpressionParserLexar
{
    private function lex(iterable $input): Generator
    {
        $state = ExpressionParserLexarState::YYSTART;
        $remaining = "";
        foreach ($input as $buffer) {
            $buffer = $remaining . $buffer;
            $mark   = 0;
            $offset = 0;
            $length = strlen($buffer);
            while ($offset < $length) {
                switch ($state) {
                    case ExpressionParserLexarState::YYSTART:
                        switch (ord($buffer[$offset])) {
                            case 0x09:case 0x0A:case 0x0D:case 0x20:
                                $mark = $offset++;
                                continue 3;
                            case 0x28:
                                $mark = $offset++;
                                yield new ExpressionParserToken(ExpressionParserType::leftgroup);
                                continue 3;
                            case 0x29:
                                $mark = $offset++;
                                yield new ExpressionParserToken(ExpressionParserType::rightgroup);
                                continue 3;
                            case 0x2A:
                                $mark = $offset++;
                                yield new ExpressionParserToken(ExpressionParserType::times);
                                continue 3;
                            case 0x2B:
                                $mark = $offset++;
                                yield new ExpressionParserToken(ExpressionParserType::plus);
                                continue 3;
                            case 0x2D:
                                $mark = $offset++;
                                yield new ExpressionParserToken(ExpressionParserType::minus);
                                continue 3;
                            case 0x2F:
                                $mark = $offset++;
                                yield new ExpressionParserToken(ExpressionParserType::division);
                                continue 3;
                            case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                                $mark = $offset++;
                                $state = ExpressionParserLexarState::YYINT;
                                continue 3;
                            case 0x5C:
                                $mark = $offset++;
                                yield new ExpressionParserToken(ExpressionParserType::modulo);
                                continue 3;
                            default:
                                throw new \Exception("Lex error");
                        }
                    case ExpressionParserLexarState::YYINT:
                        switch (ord($buffer[$offset])) {
                            case 0x2E:
                                ++$offset;
                                $state = ExpressionParserLexarState::YYFLOAT;
                                continue 3;
                            case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                                ++$offset;
                                continue 3;
                            default:
                                yield new ExpressionParserToken(ExpressionParserType::number, (int)substr($buffer, $mark, $offset));
                                $state = ExpressionParserLexarState::YYSTART;
                                continue 3;
                        }
                    case ExpressionParserLexarState::YYFLOAT:
                        switch (ord($buffer[$offset])) {
                            case 0x30:case 0x31:case 0x32:case 0x33:case 0x34:case 0x35:case 0x36:case 0x37:case 0x38:case 0x39:
                                ++$offset;
                                continue 3;
                            default:
                                yield new ExpressionParserToken(ExpressionParserType::number, (float)substr($buffer, $mark, $offset));
                                $state = ExpressionParserLexarState::YYSTART;
                                continue 3;
                        }
                }
            }
            $remaining = substr($buffer, $mark);
        }
        switch ($state) {
            case ExpressionParserLexarState::YYINT:
                yield new ExpressionParserToken(ExpressionParserType::number, (int)substr($buffer, $mark, $offset));
                break;

            case ExpressionParserLexarState::YYFLOAT:
                yield new ExpressionParserToken(ExpressionParserType::number, (float)substr($buffer, $mark, $offset));
                break;

            default:
        }
        yield null;
    }
}
