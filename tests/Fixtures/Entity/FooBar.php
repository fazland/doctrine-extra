<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class FooBar
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     */
    public $id;

    /**
     * @var mixed
     */
    public $prop;
}
