<?php

namespace SpecShaper\EncryptBundle\Tests\Unit\BlindIndex;

use PHPUnit\Framework\TestCase;
use SpecShaper\EncryptBundle\Annotations\BlindIndex;
use SpecShaper\EncryptBundle\BlindIndex\BlindIndexField;
use SpecShaper\EncryptBundle\BlindIndex\BlindIndexUpdater;
use SpecShaper\EncryptBundle\Exception\EncryptException;
use SpecShaper\EncryptBundle\Hashers\HmacBlindIndexHasher;

class BlindIndexUpdaterTest extends TestCase
{
    public function testUpdatesBlindIndexForChangedSourceField(): void
    {
        $entity = new BlindIndexUpdaterTestEntity();
        $entity->email = '  Test@Example.COM  ';
        $field = $this->createField('emailLookupHash', 'email');
        $updater = new BlindIndexUpdater(new HmacBlindIndexHasher('secret'));

        $updated = $updater->update($entity, [$field->getField() => $field], ['email' => [null, $entity->email]]);

        $this->assertTrue($updated);
        $this->assertSame(
            hash_hmac('sha256', 'test@example.com', 'secret'),
            $entity->emailLookupHash
        );
    }

    public function testSkipsUnchangedSourceField(): void
    {
        $entity = new BlindIndexUpdaterTestEntity();
        $entity->email = 'test@example.com';
        $field = $this->createField('emailLookupHash', 'email');
        $updater = new BlindIndexUpdater(new HmacBlindIndexHasher('secret'));

        $updated = $updater->update($entity, [$field->getField() => $field], ['name' => [null, 'Test']]);

        $this->assertFalse($updated);
        $this->assertNull($entity->emailLookupHash);
    }

    public function testThrowsForObjectSourceValue(): void
    {
        $entity = new BlindIndexUpdaterTestEntity();
        $entity->email = new \stdClass();
        $field = $this->createField('emailLookupHash', 'email');
        $updater = new BlindIndexUpdater(new HmacBlindIndexHasher('secret'));

        $this->expectException(EncryptException::class);

        $updater->update($entity, [$field->getField() => $field]);
    }

    private function createField(string $field, string $sourceField): BlindIndexField
    {
        return new BlindIndexField(
            $field,
            new \ReflectionProperty(BlindIndexUpdaterTestEntity::class, $field),
            $sourceField,
            new \ReflectionProperty(BlindIndexUpdaterTestEntity::class, $sourceField),
            BlindIndex::NORMALIZE_LOWERCASE
        );
    }
}

class BlindIndexUpdaterTestEntity
{
    public mixed $email = null;

    public ?string $emailLookupHash = null;

    public ?string $name = null;
}
