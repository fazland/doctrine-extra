<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\ODM\MongoDB;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Fazland\DoctrineExtra\ODM\MongoDB\DocumentIterator;
use Fazland\DoctrineExtra\ODM\MongoDB\DocumentRepository;
use Fazland\DoctrineExtra\Tests\Fixtures\Document\FooBar;
use Fazland\DoctrineExtra\Tests\Mock\ODM\MongoDB\DocumentManagerTrait;
use Fazland\DoctrineExtra\Tests\Mock\ODM\MongoDB\Repository;
use MongoDB\Model\BSONDocument;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DocumentRepositoryTest extends TestCase
{
    use DocumentManagerTrait;

    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $documentManager = $this->getDocumentManager();
        $class = new ClassMetadata(FooBar::class);
        $documentManager->getMetadataFactory()->setMetadataFor(FooBar::class, $class);

        $class->mapField(['fieldName' => 'id', 'type' => 'id']);
        $class->setIdentifier('id');

        $this->repository = new Repository($documentManager, $documentManager->getUnitOfWork(), $class);
    }

    public function testAllShouldReturnAnEntityIterator(): void
    {
        $this->collection->find([], Argument::any())->willReturn(new \ArrayIterator([]));
        self::assertInstanceOf(DocumentIterator::class, $this->repository->all());
    }

    public function testCountWillReturnRowCount(): void
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

        self::assertSame(42, $this->repository->count());
    }

    public function testFindOneByCachedShouldCheckCache(): void
    {
        self::markTestSkipped('Mongo ODM does not support result cache');
    }

    /**
     * @expectedException \Doctrine\ORM\NonUniqueResultException
     */
    public function testFindOneByCachedShouldThrowIdNonUniqueResultHasBeenReturned(): void
    {
        self::markTestSkipped('Mongo ODM does not support result cache');
    }

    public function testFindByCachedShouldCheckCache(): void
    {
        self::markTestSkipped('Mongo ODM does not support result cache');
    }

    public function testFindByCachedShouldFireTheCorrectQuery(): void
    {
        self::markTestSkipped('Mongo ODM does not support result cache');
    }

    public function testGetShouldReturnADocument(): void
    {
        $this->collection
            ->find(new BSONDocument(['_id' => '5a3d346ab7f26e18ba119308']), Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn(new \ArrayIterator([
                [
                    '_id' => '5a3d346ab7f26e18ba119308',
                    'id' => '5a3d346ab7f26e18ba119308',
                ],
            ]))
        ;

        $obj1 = $this->repository->get('5a3d346ab7f26e18ba119308');

        self::assertInstanceOf(FooBar::class, $obj1);
        self::assertEquals('5a3d346ab7f26e18ba119308', $obj1->id);
    }

    /**
     * @expectedException \Fazland\DoctrineExtra\Exception\NoResultExceptionInterface
     */
    public function testGetShouldThrowIfNoResultIsFound(): void
    {
        $this->collection
            ->find(new BSONDocument(['_id' => '5a3d346ab7f26e18ba119308']), Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn(new \ArrayIterator([]))
        ;

        $this->repository->get('5a3d346ab7f26e18ba119308');
    }

    public function testGetOneByShouldReturnADocument(): void
    {
        $this->collection
            ->find(new BSONDocument(['_id' => '5a3d346ab7f26e18ba119308']), Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn(new \ArrayIterator([
                [
                    '_id' => '5a3d346ab7f26e18ba119308',
                    'id' => '5a3d346ab7f26e18ba119308',
                ],
            ]))
        ;

        $obj1 = $this->repository->getOneBy(['id' => '5a3d346ab7f26e18ba119308']);

        self::assertInstanceOf(FooBar::class, $obj1);
        self::assertEquals('5a3d346ab7f26e18ba119308', $obj1->id);
    }

    /**
     * @expectedException \Fazland\DoctrineExtra\Exception\NoResultExceptionInterface
     */
    public function testGetOneByShouldThrowIfNoResultIsFound(): void
    {
        $this->collection
            ->find(new BSONDocument(['_id' => '5a3d346ab7f26e18ba119308']), Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn(new \ArrayIterator([]))
        ;

        $this->repository->getOneBy(['id' => '5a3d346ab7f26e18ba119308']);
    }

    public function testGetOneByCachedShouldCheckTheCache(): void
    {
        self::markTestSkipped('Mongo ODM does not support result cache');
    }

    public function testRepositoryIsInstanceOfDocumentRepository(): void
    {
        $class = \get_class($this->documentManager->getRepository(FooBar::class));
        self::assertTrue(DocumentRepository::class === $class || \is_subclass_of($class, DocumentRepository::class));
    }
}
