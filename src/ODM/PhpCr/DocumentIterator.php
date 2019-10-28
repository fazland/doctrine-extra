<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\PhpCr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Doctrine\ODM\PHPCR\Query\QueryException;
use Fazland\DoctrineExtra\IteratorTrait;
use Fazland\DoctrineExtra\ObjectIteratorInterface;
use PHPCR\Query\QueryResultInterface;

/**
 * This class allows iterating a query iterator for a single entity query.
 */
class DocumentIterator implements ObjectIteratorInterface
{
    use IteratorTrait;

    /**
     * @var \Iterator
     */
    private $internalIterator;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var int|null
     */
    private $_totalCount;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = clone $queryBuilder;
        $this->apply();
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->_totalCount) {
            $queryBuilder = clone $this->queryBuilder;

            /** @var QueryResultInterface $result */
            $result = $queryBuilder->getQuery()->getResult(Query::HYDRATE_PHPCR);
            $this->_totalCount = \count($result->getRows());
        }

        return $this->_totalCount;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->getIterator()->next();

        $this->_current = null;
        $this->_currentElement = $this->getIterator()->current();

        return $this->current();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->getIterator()->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return $this->getIterator()->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->_current = null;
        $this->getIterator()->rewind();
        $this->_currentElement = $this->getIterator()->current();
    }

    /**
     * Gets the iterator.
     *
     * @return \Iterator
     */
    private function getIterator(): \Iterator
    {
        if (null !== $this->internalIterator) {
            return $this->internalIterator;
        }

        $query = $this->queryBuilder->getQuery();

        try {
            $this->internalIterator = $query->iterate();
        } catch (QueryException $e) {
            /** @var ArrayCollection $result */
            $result = $query->getResult();
            $this->internalIterator = new \ArrayIterator(\array_values($result->toArray()));
        }

        $this->_currentElement = $this->internalIterator->current();

        return $this->internalIterator;
    }
}
