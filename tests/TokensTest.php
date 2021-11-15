<?php

declare(strict_types=1);

namespace davekok\lalr1\tests;

use davekok\lalr1\Symbol;
use davekok\lalr1\SymbolType;
use davekok\lalr1\Token;
use davekok\lalr1\Tokens;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \davekok\lalr1\Tokens
 * @covers ::__construct
 * @covers \davekok\lalr1\Token::__construct
 * @uses \davekok\lalr1\SymbolType
 * @uses \davekok\lalr1\Symbol
 */
class TokensTest extends TestCase
{
    public function createToken(): Token
    {
        return new Token(new Symbol(SymbolType::LEAF, "\0", "symname", 0), "value");
    }

    /**
     * @covers \davekok\lalr1\Token::__toString
     */
    public function testToken(): void
    {
        static::assertSame(
            '{"symbol":{"type":"leaf","key":"\u0000","name":"symname","precedence":0},"value":"value"}',
            (string)$this->createToken()
        );
    }

    /**
     * @covers ::push
     * @covers ::get
     */
    public function testPush(): void
    {
        $token0 = $this->createToken();
        $token1 = $this->createToken();
        $tokens = new Tokens();
        $tokens->push($token0);
        $tokens->push($token1);
        static::assertSame($token0, $tokens->get(0));
        static::assertNotSame($token0, $tokens->get(1));
        static::assertSame($token1, $tokens->get(1));
        static::assertNotSame($token1, $tokens->get(0));
    }

    /**
     * @covers ::push
     * @covers ::reset
     * @covers ::get
     */
    public function testReset(): void
    {
        $token0 = $this->createToken();
        $token1 = $this->createToken();
        $tokens = new Tokens();
        $tokens->push($token0);
        $tokens->reset();
        $tokens->push($token1);
        static::assertSame($token1, $tokens->get(0));
    }

    /**
     * @covers ::push
     * @covers ::pop
     */
    public function testPop(): void
    {
        $token0 = $this->createToken();
        $token1 = $this->createToken();
        $tokens = new Tokens();
        $tokens->push($token0);
        $tokens->push($token1);
        static::assertSame($token1, $tokens->pop());
        static::assertSame($token0, $tokens->pop());
    }

    /**
     * @covers ::push
     * @covers ::last
     */
    public function testLast(): void
    {
        $token0 = $this->createToken();
        $token1 = $this->createToken();
        $tokens = new Tokens();
        $tokens->push($token0);
        static::assertSame($token0, $tokens->last());
        $tokens->push($token1);
        static::assertSame($token1, $tokens->last());
    }

    /**
     * @covers ::push
     * @covers ::rangeFrom
     */
    public function testRangeFrom(): void
    {
        $token0 = $this->createToken();
        $token1 = $this->createToken();
        $token2 = $this->createToken();
        $token3 = $this->createToken();
        $tokens = new Tokens();
        $tokens->push($token0);
        $tokens->push($token1);
        $tokens->push($token2);
        $tokens->push($token3);
        static::assertSame([$token0,$token1,$token2,$token3], $tokens->rangeFrom(0));
        static::assertSame([$token1,$token2,$token3], $tokens->rangeFrom(1));
        static::assertSame([$token2,$token3], $tokens->rangeFrom(2));
        static::assertSame([$token3], $tokens->rangeFrom(3));
    }

    /**
     * @covers ::push
     * @covers ::replace
     * @covers ::rangeFrom
     */
    public function testReplace(): void
    {
        $token0 = $this->createToken();
        $token1 = $this->createToken();
        $token2 = $this->createToken();
        $token3 = $this->createToken();
        $replacement = $this->createToken();
        $tokens = new Tokens();
        $tokens->push($token0);
        $tokens->push($token1);
        $tokens->push($token2);
        $tokens->push($token3);
        $tokens->replace(1, 2, $replacement);
        static::assertSame([$token0,$replacement,$token3], $tokens->rangeFrom(0));
    }

    /**
     * @covers ::push
     * @covers ::count
     * @covers ::replace
     * @covers ::pop
     */
    public function testCount(): void
    {
        $token0 = $this->createToken();
        $token1 = $this->createToken();
        $token2 = $this->createToken();
        $token3 = $this->createToken();
        $replacement = $this->createToken();
        $tokens = new Tokens();
        static::assertSame(0, $tokens->count());
        $tokens->push($token0);
        static::assertSame(1, $tokens->count());
        $tokens->push($token1);
        static::assertSame(2, $tokens->count());
        $tokens->push($token2);
        static::assertSame(3, $tokens->count());
        $tokens->push($token3);
        static::assertSame(4, $tokens->count());
        $tokens->replace(1, 2, $replacement);
        static::assertSame(3, $tokens->count());
        $tokens->pop();
        static::assertSame(2, $tokens->count());
        $tokens->pop();
        static::assertSame(1, $tokens->count());
        $tokens->pop();
        static::assertSame(0, $tokens->count());
    }

    /**
     * @covers ::push
     * @covers ::count
     * @covers ::__toString
     */
    public function testToString(): void
    {
        $token0 = $this->createToken();
        $tokens = new Tokens();
        $tokens->push($token0);
        static::assertSame(
            '{"count":1,"tokens":[{"symbol":{"type":"leaf","key":"\u0000","name":"symname","precedence":0},"value":"value"}]}',
            (string)$tokens
        );
    }
}
