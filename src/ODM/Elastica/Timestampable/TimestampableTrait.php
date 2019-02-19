<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\Elastica\Timestampable;

use Fazland\DoctrineExtra\Timestampable\TimestampableTrait as BaseTrait;
use Fazland\ODM\Elastica\Annotation as ElasticaODM;

trait TimestampableTrait
{
    use BaseTrait;

    /**
     * @var \DateTimeInterface
     *
     * @ElasticaODM\Field(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ElasticaODM\Field(type="datetime_immutable")
     */
    private $updatedAt;
}
