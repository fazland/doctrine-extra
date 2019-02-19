<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Entity;

use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a time span with a start time and an end time.
 * Start and end times are optional.
 *
 * @ORM\Embeddable()
 */
class TimeSpan
{
    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private $start;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private $end;

    /**
     * Gets the period start time, if set.
     *
     * @return \DateTimeImmutable
     */
    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * Gets the period end time, if set.
     *
     * @return \DateTimeImmutable
     */
    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * Updates the time span.
     *
     * @param \DateTimeImmutable|null $start
     * @param \DateTimeImmutable|null $end
     */
    public function update(?\DateTimeImmutable $start, ?\DateTimeImmutable $end): void
    {
        if (null !== $start && null !== $end && $start > $end) {
            throw new \InvalidArgumentException('Start cannot be greater than end');
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Checks whether the given date time is between the start and the end of
     * this time span. Null limits are treated as Infinite.
     *
     * @param \DateTimeInterface $reference
     *
     * @return bool
     */
    public function contains(\DateTimeInterface $reference): bool
    {
        $reference = Chronos::instance($reference);

        if (null !== $this->start && $reference->lt(Chronos::instance($this->start))) {
            return false;
        }

        if (null !== $this->end && $reference->gte(Chronos::instance($this->end))) {
            return false;
        }

        return true;
    }
}
