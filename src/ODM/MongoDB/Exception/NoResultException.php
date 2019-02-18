<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ODM\MongoDB\Exception;

use Doctrine\ODM\MongoDB\MongoDBException;
use Fazland\DoctrineExtra\Exception\NoResultExceptionInterface;

class NoResultException extends MongoDBException implements NoResultExceptionInterface
{
    public function __construct()
    {
        parent::__construct('No result was found for query although at least one document was expected.');
    }
}
