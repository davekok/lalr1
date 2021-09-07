<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

use Attribute;
use IteratorAggregate;
use ArrayIterator;
use Traversable;
use Exception;

#[Attribute]
class Types implements IteratorAggregate
{
    private readonly array $typesMappedByKey;
    private readonly array $typesMappedByName;
    public readonly FinalType $finalType;

    public function __construct(Type ...$types)
    {
        $typesMappedByKey = [];
        $typesMappedByName = [];
        foreach ($types as $key => $type) {
            $type->setKey($key);
            $typesMappedByKey[$key] = $type;
            $typesMappedByName[$type->name] = $type;
            if ($type instanceof FinalType) {
                if (isset($this->finalType)) {
                    throw new Exception("There can be only one final type.");
                }
                $this->finalType = $type;
            }
        }
        $this->typesMappedByKey = $typesMappedByKey;
        $this->typesMappedByName = $typesMappedByName;
    }

    public function getByKey(string $key): ?Type
    {
        return $this->typesMappedByKey[$key] ?? null;
    }

    public function getByName(string $name): ?Type
    {
        return $this->typesMappedByName[$name] ?? null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->typesMappedByKey));
    }
}
