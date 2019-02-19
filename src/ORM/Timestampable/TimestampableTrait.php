<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Timestampable;

use Doctrine\ORM\Mapping as ORM;
use Fazland\DoctrineExtra\Timestampable\TimestampableTrait as BaseTrait;

trait TimestampableTrait
{
    use BaseTrait;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetimetz_immutable")
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetimetz_immutable")
     */
    private $updatedAt;
}
