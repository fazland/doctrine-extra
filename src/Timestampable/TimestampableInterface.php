<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Timestampable;

interface TimestampableInterface
{
    /**
     * Returns the creation \DateTime of current object.
     */
    public function getCreatedAt(): \DateTimeInterface;

    /**
     * Returns the last update \DateTime of current object.
     */
    public function getUpdatedAt(): \DateTimeInterface;

    /**
     * Updates last update \DateTime. Implementors MUST set the property to now (e.g. $updatedAt = new \DateTime()).
     *
     * @return TimestampableInterface
     */
    public function updateTimestamp(): self;
}
