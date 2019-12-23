<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra;

interface ObjectIteratorInterface extends \Iterator, \Countable
{
    /**
     * Registers a callable to apply to each element of the iterator.
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function apply(?callable $callable = null): self;
}
