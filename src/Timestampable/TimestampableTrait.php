<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Timestampable;

use Cake\Chronos\Chronos;

/**
 * @property \DateTimeInterface createdAt
 * @property \DateTimeInterface updatedAt
 */
trait TimestampableTrait
{
    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(): TimestampableInterface
    {
        $this->updatedAt = Chronos::now();

        return $this;
    }
}
