<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Fazland\DoctrineExtra\TimeSpan\TimeSpanTrait;

/**
 * @ODM\EmbeddedDocument()
 */
class TimeSpan
{
    use TimeSpanTrait;

    /**
     * @ODM\Field(type="date")
     */
    private ?\DateTimeImmutable $start;

    /**
     * @ODM\Field(type="date")
     */
    private ?\DateTimeImmutable $end;
}
