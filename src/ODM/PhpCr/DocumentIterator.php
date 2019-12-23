<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\PhpCr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\QueryException;
use Fazland\DoctrineExtra\ObjectIteratorInterface;

/**
 * This class allows iterating a query iterator for a single entity query.
 */
class DocumentIterator implements ObjectIteratorInterface
{
    use IteratorTrait;

    private ?\Iterator $internalIterator;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = clone $queryBuilder;
        $this->internalIterator = null;
        $this->totalCount = null;

        $this->apply();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->getIterator()->next();

        $this->current = null;
        $this->currentElement = $this->getIterator()->current();

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
        $this->current = null;
        $this->getIterator()->rewind();
        $this->currentElement = $this->getIterator()->current();
    }

    /**
     * Gets the iterator.
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

        $this->currentElement = $this->internalIterator->current();

        return $this->internalIterator;
    }
}
