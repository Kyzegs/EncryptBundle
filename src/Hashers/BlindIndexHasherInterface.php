<?php

namespace SpecShaper\EncryptBundle\Hashers;

use SpecShaper\EncryptBundle\Annotations\BlindIndex;

interface BlindIndexHasherInterface
{
    public function hash(?string $value, string $normalizer = BlindIndex::NORMALIZE_NONE): ?string;
}
