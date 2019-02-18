<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Entity;

use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Fazland\DoctrineExtra\Timestampable\TimestampableInterface;
use Fazland\DoctrineExtra\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="foo_timestampable")
 */
class FooTimestampable implements TimestampableInterface
{
    use TimestampableTrait;

    public const ID = 42;
    public const NEW_ID = 150;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    public function __construct()
    {
        $this->id = self::ID;
        $this->createdAt = Chronos::now();
        $this->updateTimestamp();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function changeId(): void
    {
        $this->id = self::NEW_ID;
    }
}
