<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Exception;

/**
 * Exception thrown when a query unexpectedly returns more than one result.
 */
interface NonUniqueResultExceptionInterface extends \Throwable
{
}
