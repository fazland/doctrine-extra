<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Enable;

interface EnableInterface
{
    /**
     * Whether the object is enabled or not.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Enables the current object.
     *
     * @return self
     */
    public function enable(): self;

    /**
     * Disables the current object.
     *
     * @return self
     */
    public function disable(): self;
}
