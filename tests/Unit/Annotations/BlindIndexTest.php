<?php

namespace SpecShaper\EncryptBundle\Tests\Unit\Annotations;

use PHPUnit\Framework\TestCase;
use SpecShaper\EncryptBundle\Annotations\BlindIndex;

class BlindIndexTest extends TestCase
{
    public function testSourceFieldAndNormalizerCanBeConfigured(): void
    {
        $attribute = new BlindIndex(
            sourceField: 'email',
            normalizer: BlindIndex::NORMALIZE_LOWERCASE
        );

        $this->assertSame('email', $attribute->getSourceField());
        $this->assertSame(BlindIndex::NORMALIZE_LOWERCASE, $attribute->getNormalizer());
    }
}
