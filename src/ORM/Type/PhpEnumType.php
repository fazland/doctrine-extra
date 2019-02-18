<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;

final class PhpEnumType extends Type
{
    /**
     * @var string
     */
    private $name = 'enum';

    /**
     * @var string
     */
    private $enumClass = Enum::class;

    /**
     * @var bool
     */
    private $multiple = false;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        if ($this->multiple) {
            return $platform->getJsonTypeDeclarationSQL([]);
        }

        return $platform->getVarcharTypeDeclarationSQL([]);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $valueToEnumConverter = function ($enumValue): Enum {
            if (! $this->enumClass::isValid($enumValue)) {
                throw ConversionException::conversionFailed($enumValue, $this->name);
            }

            return new $this->enumClass($enumValue);
        };

        if (! $this->multiple) {
            return $valueToEnumConverter($value);
        }

        return \array_map($valueToEnumConverter, \json_decode($value, true));
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        $enumToValueConverter = function (Enum $enum): string {
            if (! $enum instanceof $this->enumClass) {
                throw ConversionException::conversionFailedInvalidType($enum, $this->name, [$this->enumClass]);
            }

            return (string) $enum;
        };

        if (! $this->multiple) {
            return $enumToValueConverter($value);
        }

        return \json_encode(\array_map($enumToValueConverter, $value));
    }

    public static function registerEnumType($typeNameOrEnumClass, $enumClass = null): void
    {
        $typeName = $typeNameOrEnumClass;
        $enumClass = $enumClass ?? $typeNameOrEnumClass;

        if (! \is_subclass_of($enumClass, Enum::class)) {
            throw new \InvalidArgumentException('Provided enum class "'.$enumClass.'" is not valid. Enums must extend "'.Enum::class.'"');
        }

        // Register and customize the type
        self::addType($typeName, static::class);

        /** @var PhpEnumType $type */
        $type = self::getType($typeName);
        $type->name = $typeName;
        $type->enumClass = $enumClass;

        $multipleEnumType = "array<$typeName>";
        self::addType($multipleEnumType, static::class);

        /** @var PhpEnumType $type */
        $type = self::getType($multipleEnumType);
        $type->name = $multipleEnumType;
        $type->enumClass = $enumClass;
        $type->multiple = true;
    }

    public static function registerEnumTypes(array $types): void
    {
        foreach ($types as $typeName => $enumClass) {
            $typeName = \is_string($typeName) ? $typeName : $enumClass;
            static::registerEnumType($typeName, $enumClass);
        }
    }
}
