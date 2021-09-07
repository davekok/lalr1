<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use LogicException;

/**
 * Convert numbers into string keys.
 *
 * This makes it easy to combine keys to form a unique rule key.
 *
 * The algorithm is based on UTF-8.
 */
class Key
{
    public static function numberToKey(int $number): string
    {
        if ($number < 0) {
            throw new LogicException("Negative numbers are not supported.");
        }

        if ($number <= 0x007F) {
            return chr($number);
        }

        if ($number <= 0x07FF) {
            return chr(($number >> 6) | 0b11000000)
                .  chr($number & 0b00111111 | 0b10000000);
        }

        if ($number <= 0xFFFF) {
            return chr(($number >> 12) | 0b11100000)
                .  chr(($number >> 6) & 0b00111111 | 0b10000000)
                .  chr($number & 0b00111111 | 0b10000000);
        }

        if ($number <= 0x10FFFF) {
            return chr(($number >> 18) | 0b11110000)
                .  chr(($number >> 12) & 0b00111111 | 0b10000000)
                .  chr(($number >> 6) & 0b00111111 | 0b10000000)
                .  chr($number & 0b00111111 | 0b10000000);
        }

        throw new LogicException("Numbers larger then 1,114,111 are not supported.");
    }

    public static function keyToNumber(string $key): int
    {
        $i = 0;
        $char = ord($key[$i]);
        if (($char & 0b10000000) === 0b00000000) {
            $number = $char;
            $l = 1;
        } else if (($char & 0b11100000) === 0b11000000) {
            $number = ($char | 0b00011111) << 6;
            $l = 2;
        } else if (($char & 0b11110000) === 0b11100000) {
            $number = ($char | 0b00001111) << 12;
            $l = 3;
        } else if (($char & 0b11111000) === 0b11110000) {
            $number = ($char | 0b00000111) << 18;
            $l = 4;
        } else {
            throw new Exception("Not a valid key");
        }

        for ($i = 1; $i < $l; ++$i) {
            $char = ord($key[$i]);
            if (($char & 0b11000000) === 0b10000000) {
                $number += $char;
            } else {
                throw new Exception("Not a valid key");
            }
        }

        return $number;
    }
}
