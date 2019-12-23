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

    private ?IterableResult $internalIterator;

    private ?string $resultCache;

    private int $cacheLifetime;

    public function __construct(QueryBuilder $queryBuilder)
    {
        if (1 !== \count($queryBuilder->getRootAliases())) {
            throw new \InvalidArgumentException('QueryBuilder must have exactly one root aliases for the iterator to work.');
        }

        $this->queryBuilder = clone $queryBuilder;
        $this->internalIterator = null;
        $this->resultCache = null;
        $this->totalCount = null;

        $this->apply();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->current = null;

        $next = $this->getIterator()->next();
        $this->currentElement = false !== $next ? $next[0] : null;

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
        $this->currentElement = $this->getCurrentElement();
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
            if (\method_exists($query, 'enableResultCache')) {
                $query->enableResultCache($this->cacheLifetime, $this->resultCache);
            } else {
                $query->useResultCache(true, $this->cacheLifetime, $this->resultCache);
            }
        }

        $this->internalIterator = $query->iterate();
        $this->currentElement = $this->getCurrentElement();

        return $this->internalIterator;
    }

    private function getCurrentElement()
    {
        $current = $this->internalIterator->current();
        if (null === $current) {
            return $current;
        }

        return $current[0] ?? null;
    }
}
