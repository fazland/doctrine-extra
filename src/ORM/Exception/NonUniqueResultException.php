<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Exception;

use Doctrine\ORM\NonUniqueResultException as Base;
use Fazland\DoctrineExtra\Exception\NonUniqueResultExceptionInterface;

class NonUniqueResultException extends Base implements NonUniqueResultExceptionInterface
{
}
