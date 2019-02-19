<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Enable;

/**
 * Represents a common implementation of EnableInterface.
 */
trait EnableTrait
{
    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function enable(): EnableInterface
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disable(): EnableInterface
    {
        $this->enabled = false;

        return $this;
    }
}
