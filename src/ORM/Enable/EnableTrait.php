<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Enable;

use Doctrine\ORM\Mapping as ORM;
use Fazland\DoctrineExtra\Enable\EnableTrait as BaseTrait;

trait EnableTrait
{
    use BaseTrait;

    /**
     * Whether the object is enabled or not.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;
}
