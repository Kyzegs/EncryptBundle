<?php

namespace SpecShaper\EncryptBundle\Annotations;

use Attribute;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("ALL")
 */
#[\Attribute(Attribute::TARGET_PROPERTY)]
final class BlindIndex
{
    public const NORMALIZE_NONE = 'none';
    public const NORMALIZE_TRIM = 'trim';
    public const NORMALIZE_LOWERCASE = 'lowercase';
    public const NORMALIZE_UPPERCASE = 'uppercase';

    public function __construct(
        private readonly string $sourceField,
        private readonly string $normalizer = self::NORMALIZE_NONE
    ) {
    }

    public function getSourceField(): string
    {
        return $this->sourceField;
    }

    public function getNormalizer(): string
    {
        return $this->normalizer;
    }
}
