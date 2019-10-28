<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\ODM\PhpCr;

use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Fazland\DoctrineExtra\DBAL\DummyStatement;
use Fazland\DoctrineExtra\ODM\PhpCr\DocumentIterator;
use Fazland\DoctrineExtra\Tests\Fixtures\Document\PhpCr\FooBar;
use Fazland\DoctrineExtra\Tests\Mock\ODM\PhpCr\DocumentManagerTrait;
use PHPCR\Util\UUIDHelper;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DocumentIteratorTest extends TestCase
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

    /**
     * @var DocumentIterator
     */
    private $iterator;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

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

        $this->queryBuilder = $documentManager->createQueryBuilder()
                                              ->fromDocument(FooBar::class, 'f');

        $this->iterator = new DocumentIterator($this->queryBuilder);
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

        self::assertCount(42, $this->iterator);
    }

    public function testShouldIterateAgainstAQueryResult(): void
    {
        $this->connection->prepare('SELECT n0.path AS n0_path, n0.identifier AS n0_identifier, n0.props AS n0_props FROM phpcr_nodes n0 WHERE n0.workspace_name = ? AND n0.type IN (\'nt:unstructured\', \'rep:root\') AND (EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:class"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\') OR EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:classparents"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\'))')
            ->willReturn(new DummyStatement([
                ['n0_path' => '/42', 'n0_identifier' => 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc', 'n0_props' => \sprintf(self::FOOBAR_PROPS, 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc')],
                ['n0_path' => '/45', 'n0_identifier' => 'c3e25d6e-8666-41c1-8ae0-f67708c82140', 'n0_props' => \sprintf(self::FOOBAR_PROPS, 'c3e25d6e-8666-41c1-8ae0-f67708c82140')],
                ['n0_path' => '/48', 'n0_identifier' => '74dd3c45-fb36-41ff-a9dd-e3e975df46a8', 'n0_props' => \sprintf(self::FOOBAR_PROPS, '74dd3c45-fb36-41ff-a9dd-e3e975df46a8')],
            ]));

        $this->connection->prepare('SELECT path, parent FROM phpcr_nodes WHERE parent IN (?, ?, ?) AND workspace_name = ? ORDER BY sort_order ASC')->willReturn(new DummyStatement([]));
        $this->connection->prepare("SELECT path AS arraykey, id, path, parent, local_name, namespace, workspace_name, identifier, type, props, depth, sort_order\n                FROM phpcr_nodes WHERE workspace_name = ? AND (path = ? OR path = ? OR path = ?) ORDER BY sort_order ASC")
            ->willReturn(new DummyStatement([
                [
                    'arraykey' => '/42',
                    'id' => '1',
                    'path' => '/42',
                    'parent' => '/',
                    'local_name' => '42',
                    'namespace' => '',
                    'workspace_name' => '',
                    'identifier' => 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc',
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc'),
                    'depth' => '1',
                    'sort_order' => null,
                ],
                [
                    'arraykey' => '/48',
                    'id' => '1',
                    'path' => '/48',
                    'parent' => '/',
                    'local_name' => '48',
                    'namespace' => '',
                    'workspace_name' => '',
                    'identifier' => '74dd3c45-fb36-41ff-a9dd-e3e975df46a8',
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, '74dd3c45-fb36-41ff-a9dd-e3e975df46a8'),
                    'depth' => '1',
                    'sort_order' => null,
                ],
                [
                    'arraykey' => '/45',
                    'id' => '1',
                    'path' => '/45',
                    'parent' => '/',
                    'local_name' => '45',
                    'namespace' => '',
                    'workspace_name' => '',
                    'identifier' => 'c3e25d6e-8666-41c1-8ae0-f67708c82140',
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, 'c3e25d6e-8666-41c1-8ae0-f67708c82140'),
                    'depth' => '1',
                    'sort_order' => null,
                ],
            ]));

        $obj1 = new FooBar();
        $obj1->id = '/42';
        $obj2 = new FooBar();
        $obj2->id = '/45';
        $obj3 = new FooBar();
        $obj3->id = '/48';

        self::assertEquals([$obj1, $obj2, $obj3], \iterator_to_array($this->iterator));
    }

    public function testShouldCallCallableSpecifiedWithApply(): void
    {
        $this->connection->prepare('SELECT n0.path AS n0_path, n0.identifier AS n0_identifier, n0.props AS n0_props FROM phpcr_nodes n0 WHERE n0.workspace_name = ? AND n0.type IN (\'nt:unstructured\', \'rep:root\') AND (EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:class"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\') OR EXTRACTVALUE(n0.props, \'count(//sv:property[@sv:name="phpcr:classparents"]/sv:value[text()="Fazland\\\\DoctrineExtra\\\\Tests\\\\Fixtures\\\\Document\\\\PhpCr\\\\FooBar"]) > 0\'))')
            ->willReturn(new DummyStatement([
                ['n0_path' => '/42', 'n0_identifier' => 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc', 'n0_props' => \sprintf(self::FOOBAR_PROPS, 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc')],
                ['n0_path' => '/45', 'n0_identifier' => 'c3e25d6e-8666-41c1-8ae0-f67708c82140', 'n0_props' => \sprintf(self::FOOBAR_PROPS, 'c3e25d6e-8666-41c1-8ae0-f67708c82140')],
                ['n0_path' => '/48', 'n0_identifier' => '74dd3c45-fb36-41ff-a9dd-e3e975df46a8', 'n0_props' => \sprintf(self::FOOBAR_PROPS, '74dd3c45-fb36-41ff-a9dd-e3e975df46a8')],
            ]));

        $this->connection->prepare('SELECT path, parent FROM phpcr_nodes WHERE parent IN (?, ?, ?) AND workspace_name = ? ORDER BY sort_order ASC')->willReturn(new DummyStatement([]));
        $this->connection->prepare("SELECT path AS arraykey, id, path, parent, local_name, namespace, workspace_name, identifier, type, props, depth, sort_order\n                FROM phpcr_nodes WHERE workspace_name = ? AND (path = ? OR path = ? OR path = ?) ORDER BY sort_order ASC")
            ->willReturn(new DummyStatement([
                [
                    'arraykey' => '/42',
                    'id' => '1',
                    'path' => '/42',
                    'parent' => '/',
                    'local_name' => '42',
                    'namespace' => '',
                    'workspace_name' => '',
                    'identifier' => 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc',
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, 'bbe7d929-32a1-4f1e-8f4d-dda42132e6cc'),
                    'depth' => '1',
                    'sort_order' => null,
                ],
                [
                    'arraykey' => '/48',
                    'id' => '1',
                    'path' => '/48',
                    'parent' => '/',
                    'local_name' => '48',
                    'namespace' => '',
                    'workspace_name' => '',
                    'identifier' => '74dd3c45-fb36-41ff-a9dd-e3e975df46a8',
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, '74dd3c45-fb36-41ff-a9dd-e3e975df46a8'),
                    'depth' => '1',
                    'sort_order' => null,
                ],
                [
                    'arraykey' => '/45',
                    'id' => '1',
                    'path' => '/45',
                    'parent' => '/',
                    'local_name' => '45',
                    'namespace' => '',
                    'workspace_name' => '',
                    'identifier' => 'c3e25d6e-8666-41c1-8ae0-f67708c82140',
                    'type' => 'nt:unstructured',
                    'props' => \sprintf(self::FOOBAR_PROPS, 'c3e25d6e-8666-41c1-8ae0-f67708c82140'),
                    'depth' => '1',
                    'sort_order' => null,
                ],
            ]));

        $calledCount = 0;
        $this->iterator->apply(static function (FooBar $bar) use (&$calledCount): string {
            ++$calledCount;

            return $bar->id;
        });

        self::assertEquals(['/42', '/45', '/48'], \iterator_to_array($this->iterator));
        self::assertEquals(3, $calledCount);
    }
}
