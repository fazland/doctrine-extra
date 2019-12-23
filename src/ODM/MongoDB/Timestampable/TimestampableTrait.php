<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB\Timestampable;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Fazland\DoctrineExtra\Timestampable\TimestampableTrait as BaseTrait;

trait TimestampableTrait
{
    use BaseTrait;

    /**
     * @ODM\Field(type="date")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ODM\Field(type="date")
     */
    private \DateTimeInterface $updatedAt;
}
