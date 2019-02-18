<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TestEntity
{
    /**
     * @ORM\Column()
     * @ORM\Id()
     */
    public $id;
}
