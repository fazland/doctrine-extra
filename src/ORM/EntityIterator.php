<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Fazland\DoctrineExtra\ObjectIteratorInterface;

/**
 * This class allows iterating a query iterator for a single entity query.
 */
class EntityIterator implements ObjectIteratorInterface
{
    use IteratorTrait {
        current as private iteratorCurrent;
    }

    /**
     * @var IterableResult
     */
    private $internalIterator;

    /**
     * @var string
     */
    private $resultCache;

    /**
     * @var int
     */
    private $cacheLifetime;

    public function __construct(QueryBuilder $queryBuilder)
    {
        if (1 !== \count($queryBuilder->getRootAliases())) {
            throw new \InvalidArgumentException('QueryBuilder must have exactly one root aliases for the iterator to work.');
        }

        $this->queryBuilder = clone $queryBuilder;
        $this->apply();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->_current = null;
        $this->_currentElement = $this->getIterator()->next()[0];

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
        $this->_current = null;
        $this->getIterator()->rewind();
        $this->_currentElement = $this->getIterator()->current()[0];
    }

    /**
     * Request to use query result cache.
     *
     * @return EntityIterator
     */
    public function useResultCache(bool $enable, string $cacheId, ?int $lifetime): self
    {
        if (! $enable) {
            $this->resultCache = null;

            return $this;
        }

        $this->resultCache = $cacheId;
        $this->cacheLifetime = $lifetime;

        return $this;
    }

    /**
     * Gets the iterator.
     */
    private function getIterator(): IterableResult
    {
        if (null !== $this->internalIterator) {
            return $this->internalIterator;
        }

        $query = $this->queryBuilder->getQuery();
        if (null !== $this->resultCache) {
            $query->useResultCache(true, $this->cacheLifetime, $this->resultCache);
        }

        $this->internalIterator = $query->iterate();
        $this->_currentElement = $this->getIterator()->current()[0];

        return $this->internalIterator;
    }
}
