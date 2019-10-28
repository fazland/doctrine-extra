<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Document\PhpCr;

use Doctrine\ODM\PHPCR\Mapping\Annotations as ODM;

/**
 * @ODM\Document()
 */
class FooBar
{
    /**
     * @var string
     *
     * @ODM\Id(strategy="ASSIGNED")
     */
    public $id;

    /**
     * @var mixed
     */
    public $prop;
}
