<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fazland\DoctrineExtra\TimeSpan\TimeSpanTrait;

/**
 * Represents a time span with a start time and an end time.
 * Start and end times are optional.
 *
 * @ORM\Embeddable()
 */
class TimeSpan
{
    use TimeSpanTrait;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private \DateTimeImmutable $start;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private \DateTimeImmutable $end;
}
