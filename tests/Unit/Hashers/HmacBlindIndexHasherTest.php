<?php

namespace SpecShaper\EncryptBundle\Tests\Unit\Hashers;

use PHPUnit\Framework\TestCase;
use SpecShaper\EncryptBundle\Annotations\BlindIndex;
use SpecShaper\EncryptBundle\Exception\EncryptException;
use SpecShaper\EncryptBundle\Hashers\HmacBlindIndexHasher;

class HmacBlindIndexHasherTest extends TestCase
{
    public function testHashUsesHmacSha256(): void
    {
        $hasher = new HmacBlindIndexHasher('secret');

        $this->assertSame(
            hash_hmac('sha256', 'test@example.com', 'secret'),
            $hasher->hash('test@example.com')
        );
    }

    public function testLowercaseNormalizerTrimsAndLowercasesValue(): void
    {
        $hasher = new HmacBlindIndexHasher('secret');

        $this->assertSame(
            $hasher->hash('test@example.com'),
            $hasher->hash('  Test@Example.COM  ', BlindIndex::NORMALIZE_LOWERCASE)
        );
    }

    public function testNullValueStaysNull(): void
    {
        $hasher = new HmacBlindIndexHasher('secret');

        $this->assertNull($hasher->hash(null));
    }

    public function testUnknownNormalizerThrowsException(): void
    {
        $hasher = new HmacBlindIndexHasher('secret');

        $this->expectException(EncryptException::class);

        $hasher->hash('test@example.com', 'unknown');
    }
}
