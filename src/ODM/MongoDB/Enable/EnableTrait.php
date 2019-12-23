<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB\Enable;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDBODM;
use Fazland\DoctrineExtra\Enable\EnableTrait as BaseTrait;

trait EnableTrait
{
    use BaseTrait;

    /**
     * Whether the object is enabled or not.
     *
     * @MongoDBODM\Field(type="boolean")
     */
    private bool $enabled;
}
