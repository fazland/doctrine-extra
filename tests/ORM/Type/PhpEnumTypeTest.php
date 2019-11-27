<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Fazland\DoctrineExtra\ORM\Type\PhpEnumType;
use Fazland\DoctrineExtra\Tests\Fixtures\Enum\ActionEnum;
use Fazland\DoctrineExtra\Tests\Fixtures\Enum\FoobarEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PhpEnumTypeTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(Type::class);
        if ($reflection->hasProperty('typeRegistry')) {
            $property = $reflection->getProperty('typeRegistry');
            $property->setAccessible(true);
            $property->setValue(null, null);
        } else {
            $fooEnum = FoobarEnum::class;
            $multipleFooEnum = "array<$fooEnum>";
            $actionEnum = FoobarEnum::class;
            $multipleActionEnum = "array<$fooEnum>";

            foreach ([$fooEnum, $multipleFooEnum, $actionEnum, $multipleActionEnum] as $enumClass) {
                if (Type::hasType($enumClass)) {
                    Type::overrideType($enumClass, null);
                }
            }

            $reflection = new \ReflectionClass(Type::class);
            $property = $reflection->getProperty('_typesMap');
            $property->setAccessible(true);

            $value = $property->getValue(null);
            unset(
                $value[ $fooEnum ],
                $value[ $multipleFooEnum ],
                $value[ $actionEnum ],
                $value[ $multipleActionEnum ]
            );

            $property->setValue(null, $value);
        }
    }

    public function testTypesAreCorrectlyRegistered(): void
    {
        foreach ([FoobarEnum::class, ActionEnum::class] as $enumClass) {
            $multipleEnumClass = "array<$enumClass>";

            self::assertFalse(Type::hasType($enumClass));
            self::assertFalse(Type::hasType($multipleEnumClass));

            PhpEnumType::registerEnumType($enumClass);

            self::assertTrue(Type::hasType($enumClass));
            self::assertTrue(Type::hasType($multipleEnumClass));

            $type = Type::getType($enumClass);
            self::assertInstanceOf(PhpEnumType::class, $type);
            self::assertEquals($enumClass, $type->getName());

            $type = Type::getType($multipleEnumClass);
            self::assertInstanceOf(PhpEnumType::class, $type);
            self::assertEquals($multipleEnumClass, $type->getName());
        }
    }

    public function testRegisterShouldThrowIfNotAnEnumClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PhpEnumType::registerEnumType(\stdClass::class);
    }

    public function testSQLDeclarationShouldBeCorrect(): void
    {
        $platform = $this->prophesize(AbstractPlatform::class);
        $platform->getVarcharTypeDeclarationSQL(Argument::type('array'))->willReturn('VARCHAR(255)');
        $platform->getJsonTypeDeclarationSQL(Argument::type('array'))->willReturn('JSON');

        $enumClass = FoobarEnum::class;

        PhpEnumType::registerEnumType($enumClass);
        $type = Type::getType($enumClass);
        self::assertEquals('VARCHAR(255)', $type->getSQLDeclaration([], $platform->reveal()));

        $multipleEnumClass = "array<$enumClass>";
        $type = Type::getType($multipleEnumClass);
        self::assertEquals('JSON', $type->getSQLDeclaration([], $platform->reveal()));
    }

    public function testConvertToPHPValueShouldHandleNullValues(): void
    {
        $enumClass = FoobarEnum::class;
        $multipleEnumClass = "array<$enumClass>";

        PhpEnumType::registerEnumType($enumClass);

        $platform = $this->prophesize(AbstractPlatform::class);

        foreach ([$enumClass, $multipleEnumClass] as $target) {
            $type = Type::getType($target);

            self::assertNull($type->convertToPHPValue(null, $platform->reveal()));
            self::assertNull($type->convertToPHPValue('', $platform->reveal()));
        }
    }

    public function testConvertToPHPShouldReturnEnum(): void
    {
        $enumClass = FoobarEnum::class;
        $multipleEnumClass = "array<$enumClass>";
        PhpEnumType::registerEnumType($enumClass);

        $platform = $this->prophesize(AbstractPlatform::class);

        $type = Type::getType($enumClass);
        $value = $type->convertToPHPValue('foo', $platform->reveal());

        self::assertInstanceOf($enumClass, $value);
        self::assertEquals($enumClass::FOO(), $value);

        $type = Type::getType($multipleEnumClass);
        $value = $type->convertToPHPValue('["foo"]', $platform->reveal());

        self::assertTrue(\is_array($value));
        self::assertCount(1, $value);
        self::assertInstanceOf($enumClass, $value[0]);
        self::assertEquals($enumClass::FOO(), $value[0]);
    }

    public function testConvertToPHPShouldThrowIfNotAValidEnumValue(): void
    {
        $this->expectException(ConversionException::class);

        $platform = $this->prophesize(AbstractPlatform::class);

        $enumClass = FoobarEnum::class;
        PhpEnumType::registerEnumType($enumClass);

        $type = Type::getType($enumClass);
        $type->convertToPHPValue('boss', $platform->reveal());
    }

    public function testConvertToPHPShouldThrowIfNotAValidMultipleEnumValue(): void
    {
        $this->expectException(ConversionException::class);

        $platform = $this->prophesize(AbstractPlatform::class);

        $enumClass = FoobarEnum::class;
        $multipleEnumClass = "array<$enumClass>";
        PhpEnumType::registerEnumType($enumClass);

        $type = Type::getType($multipleEnumClass);
        $type->convertToPHPValue('["boss"]', $platform->reveal());
    }

    public function testConvertToDatabaseShouldHandleNullValues(): void
    {
        $platform = $this->prophesize(AbstractPlatform::class);

        $enumClass = FoobarEnum::class;
        $multipleEnumClass = "array<$enumClass>";
        PhpEnumType::registerEnumType($enumClass);

        $type = Type::getType($enumClass);
        self::assertNull($type->convertToDatabaseValue(null, $platform->reveal()));

        $type = Type::getType($multipleEnumClass);
        self::assertNull($type->convertToDatabaseValue(null, $platform->reveal()));
    }

    public function testConvertToDatabaseShouldReturnConstantValue(): void
    {
        $platform = $this->prophesize(AbstractPlatform::class);

        $enumClass = FoobarEnum::class;
        $multipleEnumClass = "array<$enumClass>";
        PhpEnumType::registerEnumType($enumClass);

        $type = Type::getType($enumClass);
        self::assertEquals('foo', $type->convertToDatabaseValue(FoobarEnum::FOO(), $platform->reveal()));

        $type = Type::getType($multipleEnumClass);
        self::assertEquals('["foo"]', $type->convertToDatabaseValue([FoobarEnum::FOO()], $platform->reveal()));
    }

    public function testConvertToDatabaseShouldThrowIfNotOfCorrectClass(): void
    {
        $this->expectException(ConversionException::class);

        $platform = $this->prophesize(AbstractPlatform::class);

        $enumClass = FoobarEnum::class;
        PhpEnumType::registerEnumType($enumClass);

        $type = Type::getType($enumClass);
        $type->convertToDatabaseValue(ActionEnum::GET(), $platform->reveal());
    }

    public function testConvertToDatabaseShouldThrowIfNotOfCorrectMultipleClass(): void
    {
        $this->expectException(ConversionException::class);

        $platform = $this->prophesize(AbstractPlatform::class);

        $enumClass = FoobarEnum::class;
        $multipleEnumClass = "array<$enumClass>";
        PhpEnumType::registerEnumType($enumClass);

        $type = Type::getType($multipleEnumClass);
        $type->convertToDatabaseValue([ActionEnum::GET()], $platform->reveal());
    }
}
