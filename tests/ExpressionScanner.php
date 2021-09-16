<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Token;
use DaveKok\LALR1\Parser;
use Exception;
use Iterator;

class ExpressionScanner implements Iterator
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
            0x28 => $this->parser->createToken("("),
            0x29 => $this->parser->createToken(")"),
            0x2A => $this->parser->createToken("*"),
            0x2B => $this->parser->createToken("+"),
            0x2D => $this->parser->createToken("-"),
            0x2F => $this->parser->createToken("/"),
            0x30, 0x31, 0x32, 0x33, 0x34, 0x35, 0x36, 0x37, 0x38, 0x39 => $this->scanNumber(),
            0x5C => $this->parser->createToken("\\"),
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

    private function scanNumber(): Token
    {
        $value = $this->grep('/((?:0|[1-9][0-9]*)(?:\.[0-9]+)?(?:[Ee][+-]?[0-9]+)?)/');
        $value = strpos($value, ".") === false ? (int)$value : (float)$value;
        return $this->parser->createToken("number", $value);
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
}
