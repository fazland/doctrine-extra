<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Timestampable;

use Cake\Chronos\Chronos;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDBODM;
use Doctrine\ORM\Mapping as ORM;
use Fazland\ODM\Elastica\Annotation as ElasticaODM;

trait TimestampableTrait
{
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetimetz_immutable")
     * @MongoDBODM\Field(type="date")
     * @ElasticaODM\Field(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetimetz_immutable")
     * @MongoDBODM\Field(type="date")
     * @ElasticaODM\Field(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return TimestampableInterface
     */
    public function updateTimestamp(): TimestampableInterface
    {
        $this->updatedAt = Chronos::now();

        return $this;
    }
}
