<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB;

use Doctrine\MongoDB\Iterator;
use Doctrine\ODM\MongoDB\Query\Builder;
use Fazland\DoctrineExtra\IteratorTrait;
use Fazland\DoctrineExtra\ObjectIteratorInterface;

/**
 * This class allows iterating a query iterator for a single entity query.
 */
class DocumentIterator implements ObjectIteratorInterface
{
    use IteratorTrait;

    private Iterator $internalIterator;

    private Builder $queryBuilder;

    private ?int $totalCount;

    public function __construct(Builder $queryBuilder)
    {
        $this->queryBuilder = clone $queryBuilder;
        $this->internalIterator = $this->queryBuilder->getQuery()->getIterator();
        $this->totalCount = null;

        $this->apply();
        $this->currentElement = $this->internalIterator->current()[0];
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->totalCount) {
            $queryBuilder = clone $this->queryBuilder;
            $queryBuilder->count();

            $this->totalCount = (int) $queryBuilder->getQuery()->execute();
        }

        return $this->totalCount;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->internalIterator->next();

        $this->current = null;
        $this->currentElement = $this->internalIterator->current();

        return $this->current();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->internalIterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return $this->internalIterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->current = null;
        $this->internalIterator->rewind();
        $this->currentElement = $this->internalIterator->current();
    }
}
