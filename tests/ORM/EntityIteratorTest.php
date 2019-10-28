<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\ORM;

use Doctrine\DBAL\Cache\ArrayStatement;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Fazland\DoctrineExtra\ORM\EntityIterator;
use Fazland\DoctrineExtra\Tests\Fixtures\Entity\FooBar;
use Fazland\DoctrineExtra\Tests\Fixtures\Entity\ForeignIdentifierEntity;
use Fazland\DoctrineExtra\Tests\Fixtures\Entity\TestEntity;
use Fazland\DoctrineExtra\Tests\Mock\ORM\EntityManagerTrait;
use PHPUnit\Framework\TestCase;

class EntityIteratorTest extends TestCase
{
    use EntityManagerTrait;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var EntityIterator
     */
    private $iterator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $metadata = new ClassMetadata(FooBar::class);
        $this->getEntityManager()->getMetadataFactory()->setMetadataFor(FooBar::class, $metadata);

        $metadata->identifier = ['id'];
        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'integer',
            'scale' => null,
            'length' => null,
            'unique' => true,
            'nullable' => false,
            'precision' => null,
        ]);
        $metadata->reflFields['id'] = new \ReflectionProperty(FooBar::class, 'id');

        $this->queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->queryBuilder->select('a')
            ->from(FooBar::class, 'a');

        $this->innerConnection
            ->query('SELECT f0_.id AS id_0 FROM FooBar f0_')
            ->willReturn(new ArrayStatement([
                ['id_0' => '42'],
                ['id_0' => '45'],
                ['id_0' => '48'],
            ]))
        ;

        $this->iterator = new EntityIterator($this->queryBuilder);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage QueryBuilder must have exactly one root aliases for the iterator to work.
     */
    public function testShouldThrowIfQueryBuilderHasMoreThanOneRootAlias(): void
    {
        $this->queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->queryBuilder->select('a')
            ->addSelect('b')
            ->from(FooBar::class, 'a')
            ->from(FooBar::class, 'b')
        ;

        new EntityIterator($this->queryBuilder);
    }

    public function testShouldBeIterable(): void
    {
        self::assertTrue(\is_iterable($this->iterator));
    }

    public function testShouldBeAnIterator(): void
    {
        self::assertInstanceOf(\Iterator::class, $this->iterator);
    }

    public function testCountShouldExecuteACountQuery(): void
    {
        $this->innerConnection
            ->query('SELECT COUNT(f0_.id) AS sclr_0 FROM FooBar f0_')
            ->willReturn(new ArrayStatement([
                ['sclr_0' => '42'],
            ]))
        ;

        self::assertCount(42, $this->iterator);
    }

    public function testCountWithOffsetShouldExecuteACountQueryWithoutOffset(): void
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('a')
            ->from(FooBar::class, 'a')
            ->setFirstResult(1)
        ;

        $this->innerConnection
            ->query('SELECT f0_.id AS id_0 FROM FooBar f0_ OFFSET 1')
            ->willReturn(new ArrayStatement([
                ['id_0' => '42'],
                ['id_0' => '45'],
                ['id_0' => '48'],
            ]));

        $this->innerConnection
            ->query('SELECT COUNT(f0_.id) AS sclr_0 FROM FooBar f0_')
            ->willReturn(new ArrayStatement([
                ['sclr_0' => '42'],
            ]))
        ;

        self::assertCount(42, new EntityIterator($queryBuilder));
    }

    public function testCountShouldWorkWithEntityWithForeignIdentifier(): void
    {
        $metadata = new ClassMetadata(ForeignIdentifierEntity::class);
        $this->getEntityManager()->getMetadataFactory()->setMetadataFor(ForeignIdentifierEntity::class, $metadata);

        $metadata->mapOneToOne([
            'fieldName' => 'id',
            'targetEntity' => FooBar::class,
            'joinColumns' => [['name' => 'id', 'unique' => true, 'nullable' => 'false']],
            'id' => true,
        ]);
        $metadata->reflFields['id'] = new \ReflectionProperty(ForeignIdentifierEntity::class, 'id');

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('a')
                     ->from(ForeignIdentifierEntity::class, 'a')
                     ->setFirstResult(1)
        ;

        $this->innerConnection
            ->query('SELECT f0_.id AS id_0 FROM ForeignIdentifierEntity f0_ OFFSET 1')
            ->willReturn(new ArrayStatement([
                ['id_0' => '42'],
                ['id_0' => '45'],
                ['id_0' => '48'],
            ]));

        $this->innerConnection
            ->query('SELECT COUNT(f0_.id) AS sclr_0 FROM ForeignIdentifierEntity f0_')
            ->willReturn(new ArrayStatement([
                ['sclr_0' => '42'],
            ]))
            ->shouldBeCalled()
        ;

        self::assertCount(42, new EntityIterator($queryBuilder));
    }

    public function testShouldIterateAgainstAQueryResult(): void
    {
        $obj1 = new FooBar();
        $obj1->id = 42;
        $obj2 = new FooBar();
        $obj2->id = 45;
        $obj3 = new FooBar();
        $obj3->id = 48;

        self::assertEquals([$obj1, $obj2, $obj3], \iterator_to_array($this->iterator));
    }

    public function testShouldCallCallableSpecifiedWithApply(): void
    {
        $calledCount = 0;
        $this->iterator->apply(function (FooBar $bar) use (&$calledCount): int {
            ++$calledCount;

            return $bar->id;
        });

        self::assertEquals([42, 45, 48], \iterator_to_array($this->iterator));
        self::assertEquals(3, $calledCount);
    }

    public function testShouldUseResultCache(): void
    {
        $metadata = new ClassMetadata(TestEntity::class);
        $this->getEntityManager()->getMetadataFactory()->setMetadataFor(TestEntity::class, $metadata);

        $metadata->identifier = ['id'];
        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'integer',
            'scale' => null,
            'length' => null,
            'unique' => true,
            'nullable' => false,
            'precision' => null,
        ]);
        $metadata->reflFields['id'] = new \ReflectionProperty(TestEntity::class, 'id');

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('a')
                     ->from(TestEntity::class, 'a')
        ;

        $this->innerConnection
            ->query('SELECT t0_.id AS id_0 FROM TestEntity t0_')
            ->willReturn($stmt = $this->prophesize(Statement::class))
            ->shouldBeCalledOnce()
        ;

        $iterator = new EntityIterator($queryBuilder);
        $iterator->useResultCache(true, 'foobar_cache', 86400);

        \iterator_to_array($iterator);

        $iterator = new EntityIterator($queryBuilder);
        $iterator->useResultCache(true, 'foobar_cache', 86400);

        \iterator_to_array($iterator);

        $iterator = new EntityIterator($queryBuilder);
        $iterator->useResultCache(true, 'foobar_cache', 86400);

        \iterator_to_array($iterator);
    }
}
