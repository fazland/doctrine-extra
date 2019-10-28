<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\PhpCr\Timestampable;

use Doctrine\ODM\PHPCR\Mapping\Annotations as ODM;
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
