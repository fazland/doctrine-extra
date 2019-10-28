<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\ODM\MongoDB;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Fazland\DoctrineExtra\ODM\MongoDB\DocumentIterator;
use Fazland\DoctrineExtra\Tests\Fixtures\Document\MongoDB\FooBar;
use Fazland\DoctrineExtra\Tests\Mock\ODM\MongoDB\DocumentManagerTrait;
use MongoDB\Model\BSONDocument;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DocumentIteratorTest extends TestCase
{
    use DocumentManagerTrait;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var DocumentIterator
     */
    private $iterator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (! \class_exists('MongoDB\BSON\Serializable')) {
            self::markTestSkipped('Mongo extension not installed');
        }

        $documentManager = $this->getDocumentManager();
        $class = new ClassMetadata(FooBar::class);
        $documentManager->getMetadataFactory()->setMetadataFor(FooBar::class, $class);

        $class->mapField(['fieldName' => 'id', 'type' => 'id']);
        $class->setIdentifier('id');

        $this->builder = $documentManager->createQueryBuilder(FooBar::class);

        $this->iterator = new DocumentIterator($this->builder);
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
        $this->database
            ->command(new BSONDocument([
                'count' => 'FooBar',
                'query' => new BSONDocument(),
                'limit' => 0,
                'skip' => 0,
            ]), Argument::any())
            ->willReturn(new \ArrayIterator([
                [
                    'n' => 42,
                    'query' => (object) [],
                    'ok' => true,
                ],
            ]))
        ;

        self::assertCount(42, $this->iterator);
    }

    public function testShouldIterateAgainstAQueryResult(): void
    {
        $this->collection->find([], Argument::any())
            ->willReturn(new \ArrayIterator([
                [
                    '_id' => 42,
                    'id' => 42,
                ],
                [
                    '_id' => 45,
                    'id' => 45,
                ],
                [
                    '_id' => 48,
                    'id' => 48,
                ],
            ]))
        ;

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
        $this->collection->find([], Argument::any())
            ->willReturn(new \ArrayIterator([
                [
                    '_id' => 42,
                    'id' => 42,
                ],
                [
                    '_id' => 45,
                    'id' => 45,
                ],
                [
                    '_id' => 48,
                    'id' => 48,
                ],
            ]))
        ;

        $calledCount = 0;
        $this->iterator->apply(function (FooBar $bar) use (&$calledCount): int {
            ++$calledCount;

            return $bar->id;
        });

        self::assertEquals([42, 45, 48], \iterator_to_array($this->iterator));
        self::assertEquals(3, $calledCount);
    }
}
