<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\ODM\PhpCr;

use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Fazland\DoctrineExtra\DBAL\DummyStatement;
use Fazland\DoctrineExtra\Exception\NoResultExceptionInterface;
use Fazland\DoctrineExtra\ODM\PhpCr\DocumentIterator;
use Fazland\DoctrineExtra\ODM\PhpCr\DocumentRepository;
use Fazland\DoctrineExtra\Tests\Fixtures\Document\PhpCr\FooBar;
use Fazland\DoctrineExtra\Tests\Mock\ODM\PhpCr\DocumentManagerTrait;
use Fazland\DoctrineExtra\Tests\Mock\ODM\PhpCr\Repository;
use PHPCR\Util\UUIDHelper;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DocumentRepositoryTest extends TestCase
{
    private const FOOBAR_PROPS = <<<XML
<sv:node xmlns:mix="http://www.jcp.org/jcr/mix/1.0" xmlns:nt="http://www.jcp.org/jcr/nt/1.0"
         xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:jcr="http://www.jcp.org/jcr/1.0"
         xmlns:sv="http://www.jcp.org/jcr/sv/1.0" xmlns:rep="internal">
    <sv:property sv:name="jcr:primaryType" sv:type="Name" sv:multi-valued="0">
        <sv:value length="15">nt:unstructured</sv:value>
    </sv:property>
    <sv:property sv:name="jcr:mixinTypes" sv:type="Name" sv:multi-valued="1"><sv:value length="13">phpcr:managed</sv:value></sv:property>
    <sv:property sv:name="phpcr:class" sv:type="String" sv:multi-valued="0"><sv:value length="58">Fazland\DoctrineExtra\Tests\Fixtures\Document\PhpCr\FooBar</sv:value></sv:property>
    <sv:property sv:name="jcr:uuid" sv:type="String" sv:multi-valued="0"><sv:value length="36">%s</sv:value></sv:property>
</sv:node>
XML;

    use DocumentManagerTrait;

    private DocumentRepository $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $documentManager = $this->getDocumentManager();
        $class = new ClassMetadata(FooBar::class);
        $class->initializeReflection(new RuntimeReflectionService());
        $documentManager->getMetadataFactory()->setMetadataFor(FooBar::class, $class);

        $class->mapField(['fieldName' => 'id', 'type' => 'id', 'strategy' => 'ASSIGNED']);
        $class->setIdentifier('id');

        $this->repository = new Repository($documentManager, $class);
    }

    public function testAllShouldReturnADocumentIterator(): void
    {
        self::assertInstanceOf(DocumentIterator::class, $this->repository->all());
    }

    public function testCountWillReturnRowCount(): void
    {
        // There's no COUNT() agg function in JCR SQL2. We need to generate all the results and count.

        $nodes1 = $nodes2 = [];
        for ($i = 0; $i < 42; ++$i) {
            $id = UUIDHelper::generateUUID();
            $nodes1[] = ['n0_path' => '/path_'.$i, 'n0_identifier' => $id, 'n0_props' => \sprintf(self::FOOBAR_PROPS, $id)];
            $nodes2[] = [
                'arraykey' => '/path_'.$i,
                'id' => '1',
                'path' => '/path_'.$i,
                'parent' => '/',
                'local_name' => ''.$i,
                'namespace' => '',
                'workspace_name' => '',
                'identifier' => $id,
                'type' => 'nt:unstructured',
                'props' => \sprintf(self::FOOBAR_PROPS, $id),
                'depth' => '1',
                'sort_order' => null,
            ];
        }

        $this->connection->prepare('SELECT n0.path AS n0_path, n0.identifier AS n0_identifier, n0.props AS n0_props FROM phpcr_nodes n0 WHERE n0.workspace_name = ? AND n0.type IN (\'nt:unstructured\', \'rep:root\') AND (EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:class"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\') OR EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:classparents"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\'))')
                         ->willReturn(new DummyStatement($nodes1));
        $this->connection->prepare(Argument::containingString("SELECT path AS arraykey, id, path, parent, local_name, namespace, workspace_name, identifier, type, props, depth, sort_order\n                FROM phpcr_nodes WHERE workspace_name = ? AND"))
                         ->willReturn(new DummyStatement($nodes2));
        $this->connection->prepare(Argument::containingString('SELECT path, parent FROM phpcr_nodes WHERE parent IN'))->willReturn(new DummyStatement([]));

        self::assertSame(42, $this->repository->count());
    }

    public function testFindOneByCachedShouldCheckCache(): void
    {
        self::markTestSkipped('PhpCr ODM does not support result cache');
    }

    public function testFindOneByCachedShouldThrowIdNonUniqueResultHasBeenReturned(): void
    {
        $this->expectException(NoResultException::class);

        self::markTestSkipped('PhpCr ODM does not support result cache');
    }

    public function testFindByCachedShouldCheckCache(): void
    {
        self::markTestSkipped('PhpCr ODM does not support result cache');
    }

    public function testFindByCachedShouldFireTheCorrectQuery(): void
    {
        self::markTestSkipped('PhpCr ODM does not support result cache');
    }

    public function testGetShouldReturnADocument(): void
    {
        $this->connection->prepare(Argument::containingString('SELECT path, parent FROM phpcr_nodes WHERE parent IN'))->willReturn(new DummyStatement([]));
        $this->connection->prepare("\n              SELECT * FROM phpcr_nodes\n              WHERE path = ?\n                AND workspace_name = ?\n              ORDER BY depth, sort_order ASC")
            ->willReturn(new DummyStatement([
                [
                    'id' => '12',
                    'path' => '/5a3d346ab7f26e18ba119308',
                    'parent' => '/',
                    'local_name' => '5a3d346ab7f26e18ba119308',
                    'namespace' => '',
                    'workspace_name' => 'default',
                    'identifier' => $id = UUIDHelper::generateUUID(),
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, $id),
                    'numerical_props' => null,
                    'depth' => 1,
                    'sort_order' => null,
                ],
            ]));

        $obj1 = $this->repository->get('/5a3d346ab7f26e18ba119308');

        self::assertInstanceOf(FooBar::class, $obj1);
        self::assertEquals('/5a3d346ab7f26e18ba119308', $obj1->id);
    }

    public function testGetShouldThrowIfNoResultIsFound(): void
    {
        $this->expectException(NoResultExceptionInterface::class);

        $this->connection->prepare("\n              SELECT * FROM phpcr_nodes\n              WHERE path = ?\n                AND workspace_name = ?\n              ORDER BY depth, sort_order ASC")
            ->willReturn(new DummyStatement([]));

        $this->repository->get('/5a3d346ab7f26e18ba119308');
    }

    public function testGetOneByShouldReturnADocument(): void
    {
        $this->connection->prepare('SELECT n0.path AS n0_path, n0.identifier AS n0_identifier, n0.props AS n0_props FROM phpcr_nodes n0 WHERE n0.workspace_name = ? AND n0.type IN (\'nt:unstructured\', \'rep:root\') AND (EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="id"]/sv:value[text()="/5a3d346ab7f26e18ba119308"]) > 0\') AND (EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:class"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\') OR EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:classparents"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\'))) LIMIT 1')
            ->willReturn(new DummyStatement([
                ['n0_path' => '/5a3d346ab7f26e18ba119308', 'n0_identifier' => 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc', 'n0_props' => \sprintf(self::FOOBAR_PROPS, 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc')],
            ]));

        $this->connection->prepare('SELECT path, parent FROM phpcr_nodes WHERE parent IN (?) AND workspace_name = ? ORDER BY sort_order ASC')->willReturn(new DummyStatement([]));
        $this->connection->prepare("SELECT path AS arraykey, id, path, parent, local_name, namespace, workspace_name, identifier, type, props, depth, sort_order\n                FROM phpcr_nodes WHERE workspace_name = ? AND (path = ?) ORDER BY sort_order ASC")
            ->willReturn(new DummyStatement([
                [
                    'arraykey' => '/5a3d346ab7f26e18ba119308',
                    'id' => '12',
                    'path' => '/5a3d346ab7f26e18ba119308',
                    'parent' => '/',
                    'local_name' => '5a3d346ab7f26e18ba119308',
                    'namespace' => '',
                    'workspace_name' => '',
                    'identifier' => 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc',
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc'),
                    'depth' => '1',
                    'sort_order' => null,
                ],
            ]));

        $obj1 = $this->repository->getOneBy(['id' => '/5a3d346ab7f26e18ba119308']);

        self::assertInstanceOf(FooBar::class, $obj1);
        self::assertEquals('/5a3d346ab7f26e18ba119308', $obj1->id);
    }

    public function testGetOneByShouldThrowIfNoResultIsFound(): void
    {
        $this->expectException(NoResultExceptionInterface::class);

        $this->connection->prepare('SELECT n0.path AS n0_path, n0.identifier AS n0_identifier, n0.props AS n0_props FROM phpcr_nodes n0 WHERE n0.workspace_name = ? AND n0.type IN (\'nt:unstructured\', \'rep:root\') AND (EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="id"]/sv:value[text()="/5a3d346ab7f26e18ba119308"]) > 0\') AND (EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:class"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\') OR EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:classparents"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\'))) LIMIT 1')
            ->willReturn(new DummyStatement([]));

        $this->repository->getOneBy(['id' => '/5a3d346ab7f26e18ba119308']);
    }

    public function testGetOneByCachedShouldCheckTheCache(): void
    {
        self::markTestSkipped('PhpCr ODM does not support result cache');
    }

    public function testRepositoryIsInstanceOfDocumentRepository(): void
    {
        $class = \get_class($this->documentManager->getRepository(FooBar::class));
        self::assertTrue(DocumentRepository::class === $class || \is_subclass_of($class, DocumentRepository::class));
    }
}
