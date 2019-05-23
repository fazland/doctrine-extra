<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM;

use Doctrine\ORM\QueryBuilder;
use Fazland\DoctrineExtra\IteratorTrait as BaseIteratorTrait;

trait IteratorTrait
{
    use BaseIteratorTrait;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var int|null
     */
    private $_totalCount;

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->_totalCount) {
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

            $this->_totalCount = (int) $queryBuilder->select('COUNT('.$distinct.$alias.')')
                ->setFirstResult(null)
                ->setMaxResults(null)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        return $this->_totalCount;
    }
}
