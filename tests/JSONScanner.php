<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Token;
use DaveKok\LALR1\Parser;
use Exception;
use Iterator;

class JSONScanner implements Iterator
{
    private ?Token $current;
    private int $key;
    private int $offset;

    public function __construct(
        private readonly Parser $parser,
        private string $buffer
    ) {}

    public function current(): Token
    {
        return $this->current;
    }

    public function key(): int
    {
        return $this->key;
    }

    public function next(): void
    {
        $this->current = $this->scan();
        ++$this->key;
    }

    public function rewind(): void
    {
        $this->key = 0;
        $this->offset = 0;
        $this->current = $this->scan();
    }

    public function valid(): bool
    {
        return $this->current !== null;
    }

    public function scan(): ?Token
    {
        if ($this->offset >= strlen($this->buffer)) {
            return null;
        }

        return match(ord($this->buffer[$this->offset++])) {
            0x09, 0x0A, 0x0D, 0x20 => $this->scanSpace(),
            0x22 => $this->scanString(),
            0x2C => $this->parser->createToken("comma"),
            0x2D, 0x30, 0x31, 0x32, 0x33, 0x34, 0x35, 0x36, 0x37, 0x38, 0x39 => $this->scanNumber(),
            0x3A => $this->parser->createToken("colon"),
            0x5B => $this->parser->createToken("opening-bracket"),
            0x5D => $this->parser->createToken("closing-bracket"),
            0x66 => $this->scanFalse(),
            0x6E => $this->scanNull(),
            0x74 => $this->scanTrue(),
            0x7B => $this->parser->createToken("opening-brace"),
            0x7D => $this->parser->createToken("closing-brace"),
            default => throw new Exception("Scan error")
        };
    }

    private function scanSpace(): Token
    {
        // skip space
        $this->grep('/([ ]+)/');

        // do next scan
        return $this->scan();
    }

    private function scanNull(): Token
    {
        if (substr_compare($this->buffer, "null", --$this->offset, 4) !== 0) {
            throw new Exception("Scan error");
        }

        $this->offset += 4;

        return $this->parser->createToken("null", null);
    }

    private function scanTrue(): Token
    {
        if (substr_compare($this->buffer, "true", --$this->offset, 4) !== 0) {
            throw new Exception("Scan error");
        }

        $this->offset += 4;

        return $this->parser->createToken("boolean", true);
    }

    private function scanFalse(): Token
    {
        if (substr_compare($this->buffer, "false", --$this->offset, 5) !== 0) {
            throw new Exception("Scan error");
        }

        $this->offset += 5;

        return $this->parser->createToken("boolean", false);
    }

    private function scanNumber(): Token
    {
        $value = $this->grep('/(-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?(?:[Ee][+-]?[0-9]+)?)/');
        $value = strpos($value, ".") === false ? (int)$value : (float)$value;
        return $this->parser->createToken("number", $value);
    }

    private function scanString(): Token
    {
        $value = $this->grep(
            '~"((?:[\x20-\x21\x23-\x5B\x5D-\x7E\x80-\xFF]|\\\\(?:["\\\\/bfnrt]|u[0-9A-Fa-f]{4}))*)"~'
        );
        $value = str_replace(['\b', '\t', '\n', '\f', '\r'], ["\x08", "\t", "\n", "\f", "\r"], $value);
        $value = preg_replace_callback(
            '/\\\\u([0-9A-Fa-f]{4})/',
            fn($m) => self::utf8(hexdec($m[1])),
            $value
        );
        return $this->parser->createToken("string", $value);
    }

    private function grep(string $regex): string
    {
        $ret = preg_match($regex, $this->buffer, $match, 0, --$this->offset);
        if ($ret !== 1) {
            if ($ret === false) $ret = "false";
            throw new Exception("Scan error {returnValue: $ret, regex: $regex, value: \"" . substr($this->buffer, $this->offset, 20) . '"}');
        }

        $this->offset += strlen($match[0]);

        return $match[1];
    }

    private static function utf8(int $codePoint): string
    {
        if ($codePoint < 0) {
            throw new LogicException("Negative code points are not supported.");
        }

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

        throw new LogicException("Code points larger then 1,114,111 are not supported.");
    }
}
