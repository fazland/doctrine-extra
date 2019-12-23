<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\PhpCr\Enable;

use Doctrine\ODM\PHPCR\Mapping\Annotations as ODM;
use Fazland\DoctrineExtra\Enable\EnableTrait as BaseTrait;

trait EnableTrait
{
    use BaseTrait;

    /**
     * Whether the object is enabled or not.
     *
     * @ODM\Field(type="boolean")
     */
    private bool $enabled;
}
