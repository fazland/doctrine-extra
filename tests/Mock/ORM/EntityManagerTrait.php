<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Mock\ORM;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Fazland\DoctrineExtra\ORM\EntityRepository;
use Fazland\DoctrineExtra\Tests\Mock\FakeMetadataFactory;
use Fazland\DoctrineExtra\Tests\Mock\Platform;
use Prophecy\Prophecy\ObjectProphecy;

trait EntityManagerTrait
{
    private ?EntityManagerInterface $entityManager = null;

    private Connection $connection;

    /**
     * @var DriverConnection|ObjectProphecy
     */
    private object $innerConnection;

    private Configuration $configuration;

    public function getEntityManager(): EntityManagerInterface
    {
        if (null !== $this->entityManager) {
            return $this->entityManager;
        }

        $this->configuration = new Configuration();

        $this->configuration->setResultCacheImpl(new ArrayCache());
        $this->configuration->setClassMetadataFactoryName(FakeMetadataFactory::class);
        $this->configuration->setMetadataDriverImpl($this->prophesize(MappingDriver::class)->reveal());
        $this->configuration->setProxyDir(\sys_get_temp_dir());
        $this->configuration->setProxyNamespace('__TMP__\\ProxyNamespace\\');
        $this->configuration->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_ALWAYS);
        $this->configuration->setDefaultRepositoryClassName(EntityRepository::class);

        $this->innerConnection = $this->prophesize(PDOConnection::class);

        $this->connection = new Connection([
            'pdo' => $this->innerConnection->reveal(),
            'platform' => new Platform(),
        ], new Driver(), $this->configuration);

        $this->entityManager = EntityManager::create($this->connection, $this->configuration);

        return $this->entityManager;
    }
}
