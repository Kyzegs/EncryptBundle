<?php

namespace SpecShaper\EncryptBundle\BlindIndex;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use SpecShaper\EncryptBundle\Annotations\BlindIndex;
use SpecShaper\EncryptBundle\Exception\EncryptException;

final class BlindIndexMetadataProvider
{
    /**
     * @var array<string, array<string, BlindIndexField>>
     */
    private array $cache = [];

    /**
     * @return array<string, array<string, BlindIndexField>>
     */
    public function getAllForObjectManager(ObjectManager $objectManager): array
    {
        $blindIndexFields = [];

        /** @var ClassMetadata[] $metadata */
        $metadata = $objectManager->getMetadataFactory()->getAllMetadata();

        foreach ($metadata as $classMeta) {
            if ($classMeta->isMappedSuperclass) {
                continue;
            }

            $fields = $this->getForClassMetadata($classMeta);

            if (!empty($fields)) {
                $blindIndexFields[$classMeta->getName()] = $fields;
            }
        }

        return $blindIndexFields;
    }

    /**
     * @return array<string, BlindIndexField>
     */
    public function getForEntity(ObjectManager $objectManager, object $entity): array
    {
        /** @var ClassMetadata $classMeta */
        $classMeta = $objectManager->getClassMetadata(get_class($entity));

        return $this->getForClassMetadata($classMeta);
    }

    /**
     * @return array<string, BlindIndexField>
     */
    public function getForClassMetadata(ClassMetadata $classMeta): array
    {
        $className = $classMeta->getName();

        if (isset($this->cache[$className])) {
            return $this->cache[$className];
        }

        $reflectionClass = new \ReflectionClass($className);
        $blindIndexFields = [];

        foreach ($reflectionClass->getProperties() as $refProperty) {
            foreach ($refProperty->getAttributes(BlindIndex::class) as $refAttribute) {
                /** @var BlindIndex $attribute */
                $attribute = $refAttribute->newInstance();
                $field = $refProperty->getName();
                $sourceField = $attribute->getSourceField();

                if (!$classMeta->hasField($sourceField)) {
                    throw new EncryptException(sprintf('Blind index source field "%s" is not mapped on "%s".', $sourceField, $className));
                }

                if (!$classMeta->hasField($field)) {
                    throw new EncryptException(sprintf('Blind index field "%s" is not mapped on "%s".', $field, $className));
                }

                $blindIndexFields[$field] = new BlindIndexField(
                    $field,
                    $classMeta->getReflectionProperty($field),
                    $sourceField,
                    $classMeta->getReflectionProperty($sourceField),
                    $attribute->getNormalizer()
                );
            }
        }

        $this->cache[$className] = $blindIndexFields;

        return $blindIndexFields;
    }
}
