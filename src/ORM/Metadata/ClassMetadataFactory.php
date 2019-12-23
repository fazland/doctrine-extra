<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Metadata;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory as Base;
use Doctrine\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;

class ClassMetadataFactory extends Base
{
    private EntityManagerInterface $entityManager;

    /**
     * {@inheritdoc}
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;

        parent::setEntityManager($entityManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function newClassMetadataInstance($className): ClassMetadataInterface
    {
        return new ClassMetadata($className, $this->entityManager->getConfiguration()->getNamingStrategy());
    }
}
