<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra;

use Doctrine\Common\Persistence\ObjectRepository as BaseRepository;
use Fazland\DoctrineExtra\Exception\NonUniqueResultExceptionInterface;
use Fazland\DoctrineExtra\Exception\NoResultExceptionInterface;

interface ObjectRepositoryInterface extends BaseRepository
{
    /**
     * Gets an iterator to traverse all the objects of the repository.
     */
    public function all(): ObjectIteratorInterface;

    /**
     * Counts entities by a set of criteria.
     */
    public function count(array $criteria = []): int;

    /**
     * Finds a single object by a set of criteria and cache the result for next calls.
     *
     * @return object|null the entity instance or NULL if the entity can not be found
     *
     * @throws NonUniqueResultExceptionInterface
     */
    public function findOneByCached(array $criteria, array $orderBy = null, int $ttl = 28800);

    /**
     * Finds objects by a set of criteria and cache the result for next calls.
     *
     * @return array The objects
     */
    public function findByCached(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        int $ttl = 28800
    );

    /**
     * Finds an object by its primary key / identifier.
     * Throws an exception if the object cannot be found.
     *
     * @param mixed      $id
     * @param mixed|null $lockMode
     * @param mixed|null $lockVersion
     *
     * @return object
     *
     * @throws NoResultExceptionInterface
     */
    public function get($id, $lockMode = null, $lockVersion = null);

    /**
     * Finds a single object by a set of criteria and cache the result for next calls.
     *
     * @return object
     *
     * @throws NoResultExceptionInterface
     * @throws NonUniqueResultExceptionInterface
     */
    public function getOneBy(array $criteria, ?array $orderBy = null);

    /**
     * Finds a single object by a set of criteria and cache the result for next calls.
     * Throws an exception if the object cannot be found.
     *
     * @return object
     *
     * @throws NoResultExceptionInterface
     * @throws NonUniqueResultExceptionInterface
     */
    public function getOneByCached(array $criteria, ?array $orderBy = null, int $ttl = 28800);
}
