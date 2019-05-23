<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ForeignIdentifierEntity
{
    /**
     * @var int
     *
     * @ORM\OneToOne(targetEntity=FooBar::class)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     */
    public $id;

    /**
     * @var mixed
     */
    public $prop;
}
