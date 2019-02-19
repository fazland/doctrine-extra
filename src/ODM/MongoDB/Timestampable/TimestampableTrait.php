<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB\Timestampable;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Fazland\DoctrineExtra\Timestampable\TimestampableTrait as BaseTrait;

trait TimestampableTrait
{
    use BaseTrait;

    /**
     * @var \DateTimeInterface
     *
     * @ODM\Field(type="date")
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ODM\Field(type="date")
     */
    private $updatedAt;
}
