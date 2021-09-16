<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

interface ParserFactoryInterface
{
    /**
     * Create a parser based on the object provided. Reflection of the object should reveal attributes.
     */
    public function createParser(object $parser, bool $debug = false): ParserInterface;
}
