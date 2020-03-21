<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
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

            $this->totalCount = (int) $queryBuilder->select('COUNT(*) AS sclr_0')
                ->setFirstResult(null)
                ->setMaxResults(null)
                ->execute()->fetchColumn()
            ;
        }

        return $this->totalCount;
    }
}
