<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\DBAL;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;

/**
 * Dummy statement serves a static result statement
 * for DBAL connection mocks.
 *
 * Use for testing purpose only.
 */
class DummyStatement implements \IteratorAggregate, Statement
{
    /**
     * @var mixed[]
     */
    private $data;

    /**
     * @var int
     */
    private $columnCount;

    /**
     * @var int
     */
    private $num;

    /**
     * @var int
     */
    private $defaultFetchMode = FetchMode::MIXED;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->columnCount = \count($data[0] ?? []);
        $this->num = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function closeCursor()
    {
        unset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function columnCount(): int
    {
        return $this->columnCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null): bool
    {
        if (null !== $arg2 || null !== $arg3) {
            throw new \InvalidArgumentException('Caching layer does not support 2nd/3rd argument to setFetchMode()');
        }

        $this->defaultFetchMode = $fetchMode;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Iterator
    {
        $data = $this->fetchAll();

        return new \ArrayIterator($data);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        if (! isset($this->data[$this->num])) {
            return false;
        }

        $row = $this->data[$this->num++];
        $fetchMode = $fetchMode ?: $this->defaultFetchMode;

        if (FetchMode::ASSOCIATIVE === $fetchMode) {
            return $row;
        }

        if (FetchMode::NUMERIC === $fetchMode) {
            return \array_values($row);
        }

        if (FetchMode::MIXED === $fetchMode) {
            return \array_merge($row, \array_values($row));
        }

        if (FetchMode::COLUMN === $fetchMode) {
            return \reset($row);
        }

        if (\PDO::FETCH_KEY_PAIR === $fetchMode) {
            if (2 !== $this->columnCount()) {
                throw new \InvalidArgumentException('Key pair fetch-style could only be used with a due column result');
            }

            [ $key, $value ] = \array_values($row);

            return [$key => $value];
        }

        throw new \InvalidArgumentException('Invalid fetch-style "'.$fetchMode.'" given for fetching result.');
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null): array
    {
        $unique = null;
        $group = null;

        if ($fetchMode & \PDO::FETCH_UNIQUE) {
            $unique = $fetchArgument ?? 0;
            $fetchMode &= ~\PDO::FETCH_UNIQUE;
        }

        if ($fetchMode & \PDO::FETCH_GROUP) {
            $group = $fetchArgument ?? 0;
            $fetchMode &= ~\PDO::FETCH_GROUP;
        }

        $unique_values = [];
        $rows = [];
        while ($row = $this->fetch($fetchMode)) {
            if (null !== $unique) {
                $unique_value = \is_int($unique) ? \array_values($row)[$unique] : $row[$unique];
                if (\in_array($unique_value, $unique_values, true)) {
                    continue;
                }

                $unique_values[] = $unique_value;
            }

            if (null !== $group) {
                $group_value = \is_int($group) ? \array_values($row)[$group] : $row[$group];
                $rows[$group_value][] = $row;
            } else {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($columnIndex = 0)
    {
        $row = $this->fetch(FetchMode::NUMERIC);

        // TODO: verify that return false is the correct behavior
        return $row[$columnIndex] ?? false;
    }

    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        // TODO
    }

    public function bindParam($column, &$variable, $type = ParameterType::STRING, $length = null)
    {
        // TODO
    }

    public function errorCode()
    {
        return null;
    }

    public function errorInfo()
    {
        return [];
    }

    public function execute($params = null): bool
    {
        return true;
    }

    public function rowCount(): int
    {
        return \count($this->data);
    }
}
