<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\PhpCr;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Fazland\DoctrineExtra\IteratorTrait as BaseIteratorTrait;
use PHPCR\Query\QueryResultInterface;

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
            $queryBuilder->setMaxResults(null);
            $queryBuilder->setFirstResult(null);

            /** @var QueryResultInterface $result */
            $result = $queryBuilder->getQuery()->getResult(Query::HYDRATE_PHPCR);
            $this->totalCount = \count($result->getRows());
        }

        return $this->totalCount;
    }
}
