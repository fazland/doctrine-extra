<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra;

trait IteratorTrait
{
    /**
     * A function to be applied to each element while iterating.
     *
     * @var callable|null
     */
    private $callable;

    /**
     * The current element from the underlying iterator.
     *
     * @var mixed
     */
    private $currentElement;

    /**
     * The current element, which results by the application
     * of the apply function.
     *
     * @var mixed
     */
    private $current;

    /**
     * {@inheritdoc}
     */
    public function apply(?callable $callable = null): ObjectIteratorInterface
    {
        if (null === $callable) {
            $callable = static function ($val) {
                return $val;
            };
        }

        $this->current = null;
        $this->callable = $callable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (! $this->valid()) {
            return null;
        }

        if (null === $this->current) {
            $this->current = \call_user_func($this->callable, $this->currentElement);
        }

        return $this->current;
    }

    /**
     * Checks if current position is valid.
     */
    abstract public function valid(): bool;
}
