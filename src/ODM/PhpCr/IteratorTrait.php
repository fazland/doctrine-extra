<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\PhpCr;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Fazland\DoctrineExtra\IteratorTrait as BaseIteratorTrait;
use PHPCR\Query\QueryResultInterface;

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
            $queryBuilder->setMaxResults(null);
            $queryBuilder->setFirstResult(null);

            /** @var QueryResultInterface $result */
            $result = $queryBuilder->getQuery()->getResult(Query::HYDRATE_PHPCR);
            $this->_totalCount = \count($result->getRows());
        }

        return $this->_totalCount;
    }
}
