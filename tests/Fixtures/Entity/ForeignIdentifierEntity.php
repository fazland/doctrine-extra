<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ForeignIdentifierEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity=FooBar::class)
     */
    public int $id;

    /**
     * @var mixed
     */
    public $prop;
}
