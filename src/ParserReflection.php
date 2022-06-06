<?php

declare(strict_types=1);

namespace davekok\parser;

use ReflectionClass;
use ReflectionMethod;
use davekok\parser\attributes\Input;
use davekok\parser\attributes\InputOutput;
use davekok\parser\attributes\Output;
use davekok\parser\attributes\Parser;
use davekok\parser\attributes\Rule;
use davekok\parser\attributes\Type;

final class ParserReflection
{
    public readonly ReflectionClass $class;
    public readonly string|null     $name;
    public readonly string          $valueType;
    public readonly bool            $lexar;
    public readonly string|null     $defaultContext;

    /** @var ParserReflectionType[] */
    public readonly array $types;

    /** @var ParserReflectionRule[] */
    public readonly array $rules;

    public function __construct(string $parser)
    {
        spl_autoload_register(self::autoload(...));
        $class      = new ReflectionClass($parser);
        spl_autoload_unregister(self::autoload(...));
        $parser     = null;
        $haveInput  = false;
        $haveOutput = false;
        $typeId     = 0;
        $types      = [];
        $rules      = [];

        // scan attributes
        foreach (self::attributes($class) as [$method, $attribute]) {
            switch ($attribute->getName()) {
                case Parser::class:
                    $parser = $attribute->newInstance();
                    continue 2;

                case Input::class:
                    $haveInput = true;
                    goto caseType;

                case InputOutput::class:
                    $haveInput = true;
                case Output::class:
                    if ($haveOutput === true) {
                        throw new ParserReflectionException("Only one Output/InputOutput attribute allowed.");
                    }
                    $haveOutput = true;
                case Type::class:
                caseType:
                    if ($parser === null) {
                        throw new ParserReflectionException("Parser attribute must be declared before types.");
                    }
                    $type = $this->type($attribute->newInstance(), $typeId++, $parser->defaultContext);
                    if (isset($types[$type->name])) {
                        throw new ParserReflectionException("Already have type with name $type->name.");
                    }
                    $types[$type->name] = $type;
                    continue 2;

                case Rule::class:
                    $rules[] = $this->rule($method, $attribute->newInstance(), $types, $parser?->defaultContext);
                    continue 2;
            }
        }

        if ($parser === null) {
            throw new ParserReflectionException("No Parser attribute, expected one.");
        }
        if ($haveOutput === false) {
            throw new ParserReflectionException("No Output or InputOutput attribute, expected one.");
        }
        if ($haveInput === false) {
            throw new ParserReflectionException("No Input or InputOutput attribute, expected at least one.");
        }
        if (count($rules) === 0) {
            throw new ParserReflectionException("No Rule attributes, expected at least one.");
        }

        $this->class          = $class;
        $this->name           = $parser->name;
        $this->valueType      = $parser->valueType;
        $this->lexar          = $parser->lexar;
        $this->defaultContext = $parser->defaultContext;
        $this->rules          = $rules;
        $this->types          = array_values($types);
    }

    /**
     * Creates temporary placeholders for generated traits. As the traits may not exist yet.
     */
    private static function autoload(string $className): void
    {
        if (str_ends_with($className, "Stitcher")) {
            [$namespace, $trait] = self::traitName($className);
            eval(<<<PHP
                $namespace

                use davekok\parser\RuleEnum;
                use davekok\parser\Token;

                trait $trait
                {
                    private function findRule(string \$key): RuleEnum|null
                    {
                        throw new \Exception("not implemented");
                    }

                    private function reduce(RuleEnum \$rule, array \$tokens): Token
                    {
                        throw new \Exception("not implemented");
                    }
                }
                PHP);
            return;
        }

        if (str_ends_with($className, "Lexar")) {
            [$namespace, $trait] = self::traitName($className);
            eval("$namespace trait $trait {}");
        }
    }

    private static function traitName(string $className): array
    {
        $offset = strrpos($className, "\\");
        if ($offset === false) {
            return ["", "$className"];
        }
        $namespace = substr($className, 0, $offset);
        $shortName = substr($className, $offset + 1);
        return ["namespace $namespace;", "$shortName"];
    }

    /**
     * Return all the attributes of the class and its methods.
     */
    private static function attributes(ReflectionClass $class): iterable
    {
        foreach ($class->getAttributes() as $attribute) {
            yield [null, $attribute];
        }
        foreach ($class->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                yield [$method, $attribute];
            }
        }
    }

    /**
     * Reflect type
     */
    private static function type(Type $type, int $id, string|null $defaultContext): ParserReflectionType
    {
        return new ParserReflectionType(
            id:         $id,
            key:        Key::createKey($id),
            name:       $type->name,
            pattern:    $type->pattern,
            input:      $type instanceof Input  || $type instanceof InputOutput,
            output:     $type instanceof Output || $type instanceof InputOutput,
            precedence: $type->precedence,
            context:    match (true) {
                is_array($type->context)  => $type->context,
                is_string($type->context) => [$type->context],
                $defaultContext !== null  => [$defaultContext],
                default                   => [""],
            }
        );
    }

    /**
     * Reflect rule
     */
    private static function rule(
        ReflectionMethod $method,
        Rule $ruleAttr,
        array $types,
        string|null $defaultContext
    ): ParserReflectionRule
    {
        // parse rule
        $parsedRule = explode(" ", $ruleAttr->rule);

        // build key and default precedence
        $key         = "";
        $precedence  = $ruleAttr->precedence;
        foreach ($parsedRule as $typeName) {
            $type = $types[$typeName] ?? throw new ParserReflectionException("No such type '$typeName'.");
            $key .= $type->key;
            if ($precedence === 0 && $type->input && $type->precedence != 0) {
                $precedence = $type->precedence;
            }
        }

        return new ParserReflectionRule(
            name:       $method->name,
            key:        $key,
            type:       $types[$ruleAttr->type]->name,
            rule:       $ruleAttr->rule,
            precedence: $precedence,
            reducer:    $method,
            binding:    self::binding($method->getParameters(), $parsedRule),
            context:    match (true) {
                is_array($ruleAttr->context)  => $ruleAttr->context,
                is_string($ruleAttr->context) => [$ruleAttr->context],
                $defaultContext !== null      => [$defaultContext],
                default                       => [""],
            },
        );
    }

    /**
     * Bind type names to method parameters.
     */
    private static function binding(array $parameters, array $parsedRule): array
    {
        $binding = [];
        foreach ($parameters as $parameter) {
            $typeIndex = 1;
            foreach ($parsedRule as $index => $typeName) {
                if ($typeName === $parameter->name) {
                    $binding[$parameter->name] = $index;
                    continue 2;
                }

                $name = preg_quote($typeName);
                if (preg_match("/^{$name}_?([1-9][0-9]*)$/", $parameter->name, $matches) === 1) {
                    $requestedTypeIndex = (int)$matches[1];
                    if ($typeIndex === $requestedTypeIndex) {
                        $binding[$parameter->name] = $index;
                        continue 2;
                    }
                    ++$typeIndex;
                    continue;
                }
            }
            throw new ParserReflectionException("Unable to detect binding for parameter $parameter->name.");
        }
        return $binding;
    }
}
