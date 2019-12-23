<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Document\PhpCr;

use Doctrine\ODM\PHPCR\Mapping\Annotations as ODM;

/**
 * @ODM\Document()
 */
class FooBar
{
    /**
     * @ODM\Id(strategy="ASSIGNED")
     */
    public string $id;

    /**
     * @var mixed
     */
    public $prop;
}
