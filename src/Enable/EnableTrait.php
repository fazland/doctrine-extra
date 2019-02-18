<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Enable;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDBODM;
use Doctrine\ORM\Mapping as ORM;
use Fazland\ODM\Elastica\Annotation as ElasticaODM;

/**
 * Represents a common implementation of EnableInterface.
 */
trait EnableTrait
{
    /**
     * Whether the object is enabled or not.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @MongoDBODM\Field(type="boolean")
     * @ElasticaODM\Field(type="boolean")
     */
    private $enabled;

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function enable(): EnableInterface
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disable(): EnableInterface
    {
        $this->enabled = false;

        return $this;
    }
}
