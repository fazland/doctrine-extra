<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Enable;

interface EnableInterface
{
    /**
     * Whether the object is enabled or not.
     */
    public function isEnabled(): bool;

    /**
     * Enables the current object.
     */
    public function enable(): self;

    /**
     * Disables the current object.
     */
    public function disable(): self;
}
