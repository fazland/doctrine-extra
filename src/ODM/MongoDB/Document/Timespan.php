<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Fazland\DoctrineExtra\TimeSpan\TimeSpanTrait;

/**
 * @ODM\EmbeddedDocument()
 */
class Timespan
{
    use TimeSpanTrait;

    /**
     * @var \DateTimeImmutable
     *
     * @ODM\Field(type="date")
     */
    private $start;

    /**
     * @var \DateTimeImmutable
     *
     * @ODM\Field(type="date")
     */
    private $end;
}
