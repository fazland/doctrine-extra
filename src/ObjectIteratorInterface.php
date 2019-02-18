<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra;

interface ObjectIteratorInterface extends \Iterator, \Countable
{
    /**
     * Registers a callable to apply to each element of the iterator.
     *
     * @param callable $func
     *
     * @return $this
     */
    public function apply(?callable $func = null): self;
}
