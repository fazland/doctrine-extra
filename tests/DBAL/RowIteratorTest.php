<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\ORM;

use Doctrine\DBAL\Cache\ArrayStatement;
use Doctrine\DBAL\Query\QueryBuilder;
use Fazland\DoctrineExtra\DBAL\RowIterator;
use Fazland\DoctrineExtra\Tests\Mock\ORM\EntityManagerTrait;
use PHPUnit\Framework\TestCase;

class RowIteratorTest extends TestCase
{
    use EntityManagerTrait;

    private QueryBuilder $queryBuilder;
    private RowIterator $iterator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->getEntityManager(); // Initialize connection

        $this->queryBuilder = $this->connection->createQueryBuilder();
        $this->queryBuilder->select('a.id')
            ->from('foobar', 'a');

        $this->innerConnection
            ->query('SELECT a.id FROM foobar a')
            ->willReturn(new ArrayStatement([
                ['id' => '42'],
                ['id' => '45'],
                ['id' => '48'],
            ]))
        ;

        $this->iterator = new RowIterator($this->queryBuilder);
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
            ->query('SELECT COUNT(*) AS sclr_0 FROM foobar a')
            ->willReturn(new ArrayStatement([
                ['sclr_0' => '42'],
            ]))
        ;

        self::assertCount(42, $this->iterator);
    }

    public function testCountWithOffsetShouldExecuteACountQueryWithoutOffset(): void
    {
        $this->queryBuilder->setFirstResult(1);

        $this->innerConnection
            ->query('SELECT a.id FROM foobar a OFFSET 1')
            ->willReturn(new ArrayStatement([
                ['id_0' => '42'],
                ['id_0' => '45'],
                ['id_0' => '48'],
            ]));

        $this->innerConnection
            ->query('SELECT COUNT(*) AS sclr_0 FROM foobar a')
            ->willReturn(new ArrayStatement([
                ['sclr_0' => '42'],
            ]))
        ;

        self::assertCount(42, new RowIterator($this->queryBuilder));
    }

    public function testShouldIterateAgainstAQueryResult(): void
    {
        $result = [
            ['id' => 42],
            ['id' => 45],
            ['id' => 48],
        ];

        self::assertEquals($result, \iterator_to_array($this->iterator));
    }

    public function testShouldCallCallableSpecifiedWithApply(): void
    {
        $calledCount = 0;
        $this->iterator->apply(function (array $row) use (&$calledCount): int {
            ++$calledCount;

            return (int) $row['id'];
        });

        self::assertEquals([42, 45, 48], \iterator_to_array($this->iterator));
        self::assertEquals(3, $calledCount);
    }
}
