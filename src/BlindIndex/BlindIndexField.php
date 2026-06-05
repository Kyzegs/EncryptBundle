<?php

namespace SpecShaper\EncryptBundle\BlindIndex;

use ReflectionProperty;

final class BlindIndexField
{
    public function __construct(
        private readonly string $field,
        private readonly ReflectionProperty $property,
        private readonly string $sourceField,
        private readonly ReflectionProperty $sourceProperty,
        private readonly string $normalizer
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getProperty(): ReflectionProperty
    {
        return $this->property;
    }

    public function getSourceField(): string
    {
        return $this->sourceField;
    }

    public function getSourceProperty(): ReflectionProperty
    {
        return $this->sourceProperty;
    }

    public function getNormalizer(): string
    {
        return $this->normalizer;
    }
}
