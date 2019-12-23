<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Mock\ODM\MongoDB;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\SchemaManager;
use Fazland\DoctrineExtra\ODM\MongoDB\DocumentRepository;
use Fazland\DoctrineExtra\Tests\Mock\FakeMetadataFactory;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

trait DocumentManagerTrait
{
    private ?DocumentManager $documentManager = null;

    /**
     * @var Client|ObjectProphecy
     */
    private object $client;

    /**
     * @var Database|ObjectProphecy
     */
    private object $database;

    /**
     * @var Collection|ObjectProphecy
     */
    private object $collection;

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    public function getDocumentManager(): DocumentManager
    {
        if (null !== $this->documentManager) {
            return $this->documentManager;
        }

        $mongoDb = null;

        $server = $this->prophesize(\MongoClient::class);
        $server->getReadPreference()->willReturn(['type' => \MongoClient::RP_PRIMARY]);
        $server->getWriteConcern()->willReturn([
            'w' => 1,
            'wtimeout' => 5000,
        ]);
        $server->selectDB('doctrine')->will(function ($args) use (&$mongoDb): \MongoDb {
            [$dbName] = $args;
            if (isset($mongoDb)) {
                return $mongoDb;
            }

            $mongoDb = new \MongoDB($this->reveal(), $dbName);

            return $mongoDb;
        });
        $server->getClient()->willReturn($this->client = $this->prophesize(Client::class));

        $this->database = $this->prophesize(Database::class);
        $this->client->selectDatabase('doctrine', Argument::any())->willReturn($this->database);

        $this->collection = $this->prophesize(Collection::class);
        $this->client->selectCollection('doctrine', 'FooBar', Argument::any())->willReturn($this->collection);
        $this->database->selectCollection('FooBar', Argument::any())->willReturn($this->collection);

        $oldCollection = $this->prophesize(\MongoCollection::class);
        $server->selectCollection(Argument::cetera())->willReturn($oldCollection);
        $oldCollection->getCollection()->willReturn($this->collection);

        $schemaManager = $this->prophesize(SchemaManager::class);
        $this->connection = new Connection($server->reveal());

        $this->configuration = new Configuration();
        $this->configuration->setHydratorDir(\sys_get_temp_dir());
        $this->configuration->setHydratorNamespace('__TMP__\\HydratorNamespace');
        $this->configuration->setProxyDir(\sys_get_temp_dir());
        $this->configuration->setProxyNamespace('__TMP__\\ProxyNamespace');
        $this->configuration->setDefaultRepositoryClassName(DocumentRepository::class);

        $this->documentManager = DocumentManager::create($this->connection, $this->configuration);

        (function () use ($schemaManager): void {
            $this->schemaManager = $schemaManager->reveal();
            $this->metadataFactory = new FakeMetadataFactory();
        })->call($this->documentManager);

        return $this->documentManager;
    }
}
