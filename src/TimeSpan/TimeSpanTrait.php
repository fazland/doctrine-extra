<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\TimeSpan;

use Cake\Chronos\Chronos;

/**
 * @property \DateTimeImmutable start
 * @property \DateTimeImmutable end
 */
trait TimeSpanTrait
{
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
