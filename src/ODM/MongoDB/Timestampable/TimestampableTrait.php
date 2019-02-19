<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB\Timestampable;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDBODM;
use Fazland\DoctrineExtra\Timestampable\TimestampableTrait as BaseTrait;

trait TimestampableTrait
{
    use BaseTrait;

    /**
     * @var \DateTimeInterface
     *
     * @MongoDBODM\Field(type="date")
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @MongoDBODM\Field(type="date")
     */
    private $updatedAt;
}
