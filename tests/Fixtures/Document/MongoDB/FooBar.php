<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Document\MongoDB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document()
 */
class FooBar
{
    /**
     * @ODM\Id()
     */
    public int $id;

    /**
     * @var mixed
     */
    public $prop;
}
