<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\Exception;

use Doctrine\ORM\NoResultException as Base;
use Fazland\DoctrineExtra\Exception\NoResultExceptionInterface;

class NoResultException extends Base implements NoResultExceptionInterface
{
}
