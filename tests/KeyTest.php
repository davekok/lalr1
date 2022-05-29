<?php

declare(strict_types=1);

namespace davekok\parser\tests;

use PHPUnit\Framework\TestCase;
use davekok\parser\Key;
use davekok\parser\ParserException;

/**
 * @covers \davekok\parser\Key::createKey
 */
class KeyTest extends TestCase
{
    public function testCreateKeyNoNegativeNumbers(): void
    {
        $this->expectException(ParserException::class, "Negative numbers are not supported.");
        Key::createKey(-1);
    }

    public function testCreateKeyNotTooLarge(): void
    {
        $this->expectException(
            ParserException::class,
            "Numbers larger then 1,114,111 are not supported."
        );
        Key::createKey(1_114_112);
    }

    public function testCreateKey(): void
    {
        $n = -1;
        for ($i = 0b0000_0000; $i <= 0b0111_1111; ++$i) {
            self::assertSame(chr($i), Key::createKey(++$n));
        }
        self::assertSame(0x007F, $n);
        for ($i = 0b1100_0010; $i <= 0b1101_1111; ++$i) {
            for ($j = 0b1000_0000; $j <= 0b1011_1111; ++$j) {
                self::assertSame(chr($i).chr($j), Key::createKey(++$n));
            }
        }
        self::assertSame(0x07FF, $n);
        $i = 0b1110_0000;
        for ($j = 0b1010_0000; $j <= 0b1011_1111; ++$j) {
            for ($k = 0b1000_0000; $k <= 0b1011_1111; ++$k) {
                self::assertSame(chr($i).chr($j).chr($k), Key::createKey(++$n));
            }
        }
        for ($i = 0b1110_0001; $i <= 0b1110_1111; ++$i) {
            for ($j = 0b1000_0000; $j <= 0b1011_1111; ++$j) {
                for ($k = 0b1000_0000; $k <= 0b1011_1111; ++$k) {
                    self::assertSame(chr($i).chr($j).chr($k), Key::createKey(++$n));
                }
            }
        }
        self::assertSame(0xFFFF, $n);
        $i = 0b1111_0000;
        for ($j = 0b1001_0000; $j <= 0b1011_1111; ++$j) {
            for ($k = 0b1000_0000; $k <= 0b1011_1111; ++$k) {
                for ($l = 0b1000_0000; $l <= 0b1011_1111; ++$l) {
                    self::assertSame(chr($i).chr($j).chr($k).chr($l), Key::createKey(++$n));
                }
            }
        }
        for ($i = 0b1111_0001; $i <= 0b1111_0011; ++$i) {
            for ($j = 0b1000_0000; $j <= 0b1011_1111; ++$j) {
                for ($k = 0b1000_0000; $k <= 0b1011_1111; ++$k) {
                    for ($l = 0b1000_0000; $l <= 0b1011_1111; ++$l) {
                        self::assertSame(chr($i).chr($j).chr($k).chr($l), Key::createKey(++$n));
                    }
                }
            }
        }
        $i = 0b1111_0100;
        for ($j = 0b1000_0000; $j <= 0b1000_1111; ++$j) {
            for ($k = 0b1000_0000; $k <= 0b1011_1111; ++$k) {
                for ($l = 0b1000_0000; $l <= 0b1011_1111; ++$l) {
                    self::assertSame(chr($i).chr($j).chr($k).chr($l), Key::createKey(++$n));
                }
            }
        }
        self::assertSame(0x10_FFFF, $n);
    }
}
