<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM;

use Doctrine\ORM\QueryBuilder;
use Fazland\DoctrineExtra\IteratorTrait as BaseIteratorTrait;

trait IteratorTrait
{
    use BaseIteratorTrait;

    private QueryBuilder $queryBuilder;

    private ?int $totalCount;

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->totalCount) {
            $queryBuilder = clone $this->queryBuilder;
            $alias = $queryBuilder->getRootAliases()[0];

            $queryBuilder->resetDQLPart('orderBy');
            $distinct = $queryBuilder->getDQLPart('distinct') ? 'DISTINCT ' : '';
            $queryBuilder->resetDQLPart('distinct');

            $em = $queryBuilder->getEntityManager();
            $metadata = $em->getClassMetadata($queryBuilder->getRootEntities()[0]);

            if ($metadata->containsForeignIdentifier) {
                $alias = 'IDENTITY('.$alias.')';
            }

            $this->totalCount = (int) $queryBuilder->select('COUNT('.$distinct.$alias.')')
                ->setFirstResult(null)
                ->setMaxResults(null)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        return $this->totalCount;
    }
}
