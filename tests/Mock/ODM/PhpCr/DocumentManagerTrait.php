<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Mock\ODM\PhpCr;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ODM\PHPCR\Configuration;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Fazland\DoctrineExtra\DBAL\DummyStatement;
use Fazland\DoctrineExtra\ODM\PhpCr\DocumentRepository;
use Fazland\DoctrineExtra\Tests\Mock\FakeMetadataFactory;
use Jackalope\Factory;
use Jackalope\Transport\DoctrineDBAL\Client;
use PHPCR\SimpleCredentials;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

function nodeTypesQuery($connection)
{
    $connection->prepare(Argument::that(static function (string $arg): bool {
        return (bool) \preg_match('/FROM\s+phpcr_type_nodes/', $arg);
    }))->will(function () {
        return new DummyStatement([
            [
                'node_name' => 'phpcr:managed',
                'node_abstract' => false,
                'node_mixin' => true,
                'node_queryable' => true,
                'node_has_orderable_child_nodes' => false,
                'node_primary_item_name' => null,
                'declared_super_type_names' => 'nt:base',
                'property_name' => 'phpcr:class',
                'property_auto_created' => false,
                'property_mandatory' => false,
                'property_protected' => false,
                'property_on_parent_version' => 1,
                'property_required_type' => 1,
                'property_multiple' => false,
                'property_fulltext_searchable' => true,
                'property_query_orderable' => true,
                'property_default_value' => null,
                'child_name' => null,
                'child_auto_created' => null,
                'child_mandatory' => null,
                'child_protected' => null,
                'child_on_parent_version' => null,
                'child_default_type' => null,
                'child_primary_types' => null,
            ],
            [
                'node_name' => 'phpcr:managed',
                'node_abstract' => false,
                'node_mixin' => true,
                'node_queryable' => true,
                'node_has_orderable_child_nodes' => false,
                'node_primary_item_name' => null,
                'declared_super_type_names' => 'nt:base',
                'property_name' => 'phpcr:classparents',
                'property_auto_created' => false,
                'property_mandatory' => false,
                'property_protected' => false,
                'property_on_parent_version' => 1,
                'property_required_type' => 1,
                'property_multiple' => true,
                'property_fulltext_searchable' => true,
                'property_query_orderable' => true,
                'property_default_value' => null,
                'child_name' => null,
                'child_auto_created' => null,
                'child_mandatory' => null,
                'child_protected' => null,
                'child_on_parent_version' => null,
                'child_default_type' => null,
                'child_primary_types' => null,
            ],
        ]);
    });
}

trait DocumentManagerTrait
{
    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    /**
     * @var PDOConnection|ObjectProphecy
     */
    private $connection;

    public function getDocumentManager(): DocumentManager
    {
        if (null === $this->documentManager) {
            $this->connection = $this->prophesize(PDOConnection::class);

            $connection = new Connection([
                'pdo' => $this->connection->reveal(),
                'platform' => new MySqlPlatform(),
            ], new Driver());

            $this->connection->prepare('SELECT 1 FROM phpcr_workspaces WHERE name = ?')->willReturn(new DummyStatement([[1]]));
            $this->connection->query('SELECT DATABASE()')->willReturn(new DummyStatement([['test_db']]));
            $this->connection->query('SELECT prefix, uri FROM phpcr_namespaces')
                ->willReturn(new DummyStatement([
                    ['prefix' => 'phpcr_locale', 'uri' => 'http://www.doctrine-project.org/projects/phpcr_odm/phpcr_locale'],
                    ['prefix' => 'phpcr', 'uri' => 'http://www.doctrine-project.org/projects/phpcr_odm'],
                ]));
            nodeTypesQuery($this->connection);

            $transport = new Client($factory = new Factory(), $connection);
            $repository = new \Jackalope\Repository($factory, $transport);
            $session = $repository->login(new SimpleCredentials('admin', 'admin'), 'default');

            $configuration = new Configuration();
            $configuration->setProxyDir(\sys_get_temp_dir());
            $configuration->setProxyNamespace('__TMP__\\ProxyNamespace');
            $configuration->setDefaultRepositoryClassName(DocumentRepository::class);

            $this->documentManager = DocumentManager::create($session, $configuration);

            (function (): void {
                $this->metadataFactory = new FakeMetadataFactory();
            })->call($this->documentManager);
        }

        return $this->documentManager;
    }
}
