<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Metadata;

use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\Mapping\ClassMetadata as BaseClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\Mapping\ReflectionEmbeddedProperty;

class ClassMetadata extends BaseClassMetadata
{
    /**
     * {@inheritdoc}
     */
    public function wakeupReflection($reflectionService): void
    {
        // Restore ReflectionClass and properties
        $this->reflClass = $reflectionService->getClass($this->name);

        $instantiatorProperty = new \ReflectionProperty(ClassMetadataInfo::class, 'instantiator');
        $instantiatorProperty->setAccessible(true);

        $instantiator = $instantiatorProperty->getValue($this);
        if (! $instantiator) {
            $instantiatorProperty->setValue($this, new Instantiator());
        }

        $parentReflectionFields = [];

        foreach ($this->embeddedClasses as $property => $embeddedClass) {
            if (isset($embeddedClass['declaredField'])) {
                $parentReflectionFields[$property] = new ReflectionEmbeddedProperty(
                    $parentReflectionFields[$embeddedClass['declaredField']],
                    $reflectionService->getAccessibleProperty(
                        $this->embeddedClasses[$embeddedClass['declaredField']]['class'],
                        $embeddedClass['originalField']
                    ),
                    $this->embeddedClasses[$embeddedClass['declaredField']]['class']
                );

                continue;
            }

            $fieldReflection = $reflectionService->getAccessibleProperty(
                $embeddedClass['declared'] ?? $this->name,
                $property
            );

            $parentReflectionFields[$property] = $fieldReflection;
            $this->reflFields[$property] = $fieldReflection;
        }

        foreach ($this->fieldMappings as $field => $mapping) {
            if (isset($mapping['declaredField'], $parentReflectionFields[$mapping['declaredField']])) {
                $this->reflFields[$field] = new ReflectionEmbeddedProperty(
                    $parentReflectionFields[$mapping['declaredField']],
                    $reflectionService->getAccessibleProperty($mapping['originalClass'], $mapping['originalField']),
                    $mapping['originalClass']
                );
                continue;
            }

            $this->reflFields[$field] = $reflectionService->getAccessibleProperty($mapping['declared'] ?? $this->name, $field);
        }

        foreach ($this->associationMappings as $key => $mapping) {
            if (isset($mapping['declaredField'], $parentReflectionFields[$mapping['declaredField']])) {
                $this->reflFields[$key] = new ReflectionEmbeddedProperty(
                    $parentReflectionFields[$mapping['declaredField']],
                    $reflectionService->getAccessibleProperty($mapping['originalClass'], $mapping['originalField']),
                    $mapping['originalClass']
                );
                continue;
            }

            $this->reflFields[$key] = $reflectionService->getAccessibleProperty($mapping['declared'] ?? $this->name, $key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function inlineEmbeddable($property, ClassMetadataInfo $embeddable): void
    {
        foreach ($embeddable->fieldMappings as $fieldMapping) {
            $fieldMapping['originalClass'] = $fieldMapping['originalClass'] ?? $embeddable->name;
            $fieldMapping['declaredField'] = isset($fieldMapping['declaredField']) ? $property.'.'.$fieldMapping['declaredField'] : $property;
            $fieldMapping['originalField'] = $fieldMapping['originalField'] ?? $fieldMapping['fieldName'];
            $fieldMapping['fieldName'] = $property.'.'.$fieldMapping['fieldName'];

            if (! empty($this->embeddedClasses[$property]['columnPrefix'])) {
                $fieldMapping['columnName'] = $this->embeddedClasses[$property]['columnPrefix'].$fieldMapping['columnName'];
            } elseif (false !== $this->embeddedClasses[$property]['columnPrefix']) {
                $fieldMapping['columnName'] = $this->namingStrategy
                    ->embeddedFieldToColumnName(
                        $property,
                        $fieldMapping['columnName'],
                        $this->reflClass->name,
                        $embeddable->reflClass->name
                    )
                ;
            }

            $this->mapField($fieldMapping);
        }

        foreach ($embeddable->associationMappings as $assocMapping) {
            if (! ($assocMapping['type'] & BaseClassMetadata::MANY_TO_ONE)) {
                continue;
            }

            $assocMapping['originalClass'] = $assocMapping['originalClass'] ?? $embeddable->name;
            $assocMapping['declaredField'] = isset($assocMapping['declaredField']) ? $property.'_'.$assocMapping['declaredField'] : $property;
            $assocMapping['originalField'] = $assocMapping['originalField'] ?? $assocMapping['fieldName'];
            $assocMapping['fieldName'] = $property.'_'.$assocMapping['fieldName'];

            $assocMapping['sourceToTargetKeyColumns'] = [];
            $assocMapping['joinColumnFieldNames'] = [];
            $assocMapping['targetToSourceKeyColumns'] = [];

            foreach ($assocMapping['joinColumns'] as &$column) {
                if (! empty($this->embeddedClasses[$property]['columnPrefix'])) {
                    $column['name'] = $this->embeddedClasses[$property]['columnPrefix'].$column['name'];
                } elseif (false !== $this->embeddedClasses[$property]['columnPrefix']) {
                    $column['name'] = $this->namingStrategy
                        ->embeddedFieldToColumnName(
                            $property,
                            $column['name'],
                            $this->reflClass->name,
                            $embeddable->reflClass->name
                        )
                    ;
                }
            }

            unset($column);
            $this->mapManyToOne($assocMapping);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAssociations(): void
    {
        foreach ($this->associationMappings as $mapping) {
            if (! \class_exists($mapping['targetEntity'])) {
                throw MappingException::invalidTargetEntityClass($mapping['targetEntity'], $this->name, $mapping['fieldName']);
            }
        }
    }
}
