<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\DBAL;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Fazland\DoctrineExtra\ObjectIteratorInterface;

class RowIterator implements ObjectIteratorInterface
{
    use IteratorTrait {
        current as private iteratorCurrent;
    }

    private ?\ArrayIterator $internalIterator;

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
        $this->current = null;

        $iterator = $this->getIterator();

        $iterator->next();
        $this->currentElement = $iterator->valid() ? $iterator->current() : null;

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
    public function current()
    {
        $this->getIterator();

        return $this->iteratorCurrent();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->current = null;
        $this->getIterator()->rewind();
        $this->currentElement = $this->internalIterator->current();
    }

    /**
     * Gets the iterator.
     */
    private function getIterator(): \ArrayIterator
    {
        if (null !== $this->internalIterator) {
            return $this->internalIterator;
        }

        $this->internalIterator = new \ArrayIterator($this->queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE));
        $this->currentElement = $this->internalIterator->current();

        return $this->internalIterator;
    }
}
