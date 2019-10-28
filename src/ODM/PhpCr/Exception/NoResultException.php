<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\PhpCr\Exception;

use Doctrine\ODM\PHPCR\Exception\RuntimeException;
use Fazland\DoctrineExtra\Exception\NoResultExceptionInterface;

class NoResultException extends RuntimeException implements NoResultExceptionInterface
{
    public function __construct()
    {
        parent::__construct('No result was found for query although at least one document was expected.');
    }
}
