<?php

declare(strict_types=1);

namespace davekok\parser;

/**
 * Convert integers into string.
 *
 * This makes it easy to create unique rule keys by concatenating symbol keys.
 */
class Key
{
    public static function createKey(int|null $number = null): string
    {
        if ($number === null) {
            return "";
        }

        if ($number < 0) {
            throw new ParserException("Negative numbers are not supported.");
        }

        // The algorithm is based on UTF-8.
        if ($number <= 0x007F) {
            return chr($number);
        }

        if ($number <= 0x07FF) {
            return chr(($number >> 6) | 0b11000000) . chr($number & 0b00111111 | 0b10000000);
        }

        if ($number <= 0xFFFF) {
            return chr(($number >> 12) | 0b11100000)
                . chr(($number >> 6) & 0b00111111 | 0b10000000)
                . chr($number & 0b00111111 | 0b10000000);
        }

        if ($number <= 0x10FFFF) {
            return chr(($number >> 18) | 0b11110000)
                . chr(($number >> 12) & 0b00111111 | 0b10000000)
                . chr(($number >> 6) & 0b00111111 | 0b10000000)
                . chr($number & 0b00111111 | 0b10000000);
        }

        throw new ParserException("Numbers larger then 1,114,111 are not supported.");
    }
}
