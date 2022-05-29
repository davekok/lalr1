<?php

declare(strict_types=1);

namespace davekok\parser;

use Generator;
use ReflectionClass;
use ReflectionMethod;
use davekok\parser\attributes\Input;
use davekok\parser\attributes\InputOutput;
use davekok\parser\attributes\Output;
use davekok\parser\attributes\Parser as ParserAttribute;
use davekok\parser\attributes\Rule as RuleAttribute;
use davekok\parser\attributes\Type as TypeAttribute;

class ParserReflection
{
    public readonly ReflectionClass $class;
    public readonly string|null $name;
    public readonly string $valueType;
    public readonly bool $lexar;

    /** @var ParserReflectionType[] */
    public readonly array $types;

    /** @var ParserReflectionRule[] */
    public readonly array $rules;

    public function __construct(string|object $parser)
    {
        $this->class = $parser instanceof ReflectionClass ? $parser : new ReflectionClass($parser);
        $parser = $this->parser();
        $this->name = $parser->name;
        $this->valueType = $parser->valueType;
        $this->lexar = $parser->lexar;
        $this->types = iterator_to_array($this->types());
        $this->rules = iterator_to_array($this->rules());
    }

    private function parser(): ParserAttribute
    {
        foreach ($this->class->getAttributes(ParserAttribute::class) as $attribute) {
            return $attribute->newInstance();
        }
        throw new ParserReflectionException("Parser attribute is missing");
    }

    private function types(): Generator
    {
        $id = 0;
        $haveInput = false;
        $haveOutput = false;
        foreach ($this->class->getAttributes() as $attribute) {
            switch ($attribute->getName()) {
                case Input::class:
                    $input = true;
                    $output = false;
                    $haveInput = true;
                    break;

                case Output::class:
                    if ($haveOutput) {
                        throw new ParserReflectionException("Output type already declared.");
                    }
                    $input = false;
                    $output = true;
                    $haveOutput = true;
                    break;

                case InputOutput::class:
                    if ($haveOutput) {
                        throw new ParserReflectionException("Output type already declared.");
                    }
                    $input = true;
                    $output = true;
                    $haveInput = true;
                    $haveOutput = true;
                    break;

                case TypeAttribute::class:
                    $input = false;
                    $output = false;
                    break;

                default:
                    continue 2;
            }
            $type = $attribute->newInstance();
            yield $type->text => new ParserReflectionType(
                id: $id,
                key: Key::createKey($id),
                name: $type->name,
                text: $type->text,
                input: $input,
                output: $output,
                precedence: $type->precedence,
            );
            ++$id;
        }
        if (!$haveInput) {
            throw new ParserReflectionException("Input type is missing, at least one expected.");
        }
        if (!$haveOutput) {
            throw new ParserReflectionException("Output type is missing, expected one.");
        }
    }

    private function rules(): Generator
    {
        foreach ($this->class->getMethods() as $method) {
            foreach ($method->getAttributes(RuleAttribute::class) as $attr) {
                $ruleAttr = $attr->newInstance();
                $key = "";
                $precedence = $ruleAttr->precedence;
                foreach (explode(" ", $ruleAttr->text) as $typeText) {
                    $type = $this->types[$typeText] ?? throw new ParserReflectionException("No such type '$typeText'");
                    $key .= $type->key;
                    if ($precedence === 0 && $type->input && $type->precedence != 0) {
                        $precedence = $type->precedence;
                    }
                }
                yield new ParserReflectionRule(
                    name: $method->name,
                    key: $key,
                    type: $this->types[$ruleAttr->type]->name,
                    text: $ruleAttr->text,
                    precedence: $precedence,
                    reducer: $method,
                );
            }
        }
    }
}
