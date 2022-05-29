<?php

declare(strict_types=1);

namespace davekok\parser;

class Key
{
    public static function createKey(int $number): string
    {
        if ($number < 0) {
            throw new ParserException("Negative ids are not supported.");
        }

        // The algorithm is based on UTF-8.
        if ($number <= 0x0000_007F) {
            return chr($number);
        }

        if ($number <= 0x0000_07FF) {
            return chr(($number >> 6) | 0b1100_0000) . chr($number & 0b0011_1111 | 0b1000_0000);
        }

        if ($number <= 0x0000_FFFF) {
            return chr(($number >> 12) | 0b1110_0000)
                . chr(($number >> 6) & 0b0011_1111 | 0b1000_0000)
                . chr($number & 0b0011_1111 | 0b1000_0000);
        }

        if ($number <= 0x0010_FFFF) {
            return chr(($number >> 18) | 0b1111_0000)
                . chr(($number >> 12) & 0b0011_1111 | 0b1000_0000)
                . chr(($number >> 6) & 0b00111111 | 0b1000_0000)
                . chr($number & 0b0011_1111 | 0b1000_0000);
        }

        throw new ParserException("Numbers larger then 1,114,111 are not supported.");
    }
}
