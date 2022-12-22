<?php

namespace App\Service;

use PDO;
use PDOException;

class AdapterWrapper
{
    public static PDO $db;

    public static function init($db): void
    {
        self::$db = $db;
    }

    public function __construct(private ?string $table = null)
    {
    }

    /**
     * @param array $set
     * @param array $where
     * @throws PDOException
     * @return int
     */
    public function update(array $set, array $where = []): int
    {
        $data = [];
        $preparedWhere = static::parseWhere($where);
        foreach ($set as $name => $value) {
            $data[] = "`{$name}` = '{$value}'";
        }
        $set = implode(', ', $data);
        $query = "UPDATE `{$this->table}` 
                SET {$set} 
                WHERE {$preparedWhere};";
        $st = self::$db->prepare($query);
        $st->execute();
        return $st->rowCount();
    }

    /**
     * @param array $where
     * @throws PDOException
     * @return int
     */
    public function delete(array $where = []): int
    {
        $preparedWhere = static::parseWhere($where);
        $query = "DELETE FROM `{$this->table}` 
                WHERE {$preparedWhere};";
        $st = self::$db->prepare($query);
        $st->execute();
        return $st->rowCount();
    }

    /**
     * @param $data
     * @throws PDOException
     * @return false|string
     */
    public function insert($data): false|string
    {
        $columns = [];
        $values = [];
        foreach ($data as $name => $value) {
            if (!is_null($value)) {
                $columns[] = "`{$name}`";
                $values[] = "'{$value}'";
            }
        }
        $columns = implode(', ', $columns);
        $values = implode(', ', $values);
        $query = "INSERT INTO `{$this->table}` ({$columns}) 
                values ({$values});";
        self::execute($query);
        return self::$db->lastInsertId();
    }

    private static function parseWhere(array $where, string $separator = "AND"): string {
        $preparedWhere = [];
        foreach ($where as $whereKey => $whereItem) {
            if (($whereKey === "OR" || $whereKey === "AND") && is_array($whereItem)) {
                $preparedWhere[] = static::parseWhere($whereItem, $whereKey);
            } else if (is_string($whereKey)) {
                $preparedWhere[] = "`{$whereKey}` = '{$whereItem}'";
            } else {
                $preparedWhere[] = "({$whereItem})";
            }
        }
        return "(" . implode(" {$separator}", $preparedWhere) . ")";
    }

    /**
     * @param string $query
     * @param array $params
     * @throws PDOException
     * @return bool
     */
    public function execute(string $query, array $params = []): bool
    {
        $st = self::$db->prepare($query);
        return $st->execute($params);
    }

    /**
     * @param array $conditions
     * @param int $mode
     * @return mixed
     */
    public function fetch(array $conditions, int $mode = PDO::FETCH_BOTH): mixed
    {
        $where = static::parseWhere($conditions);
        $query = "SELECT * FROM `{$this->table}` WHERE {$where}";
        $statement = self::$db->query($query);
        return $statement?->fetch($mode);
    }

    /**
     * @param array $conditions
     * @param int|null $limit
     * @param string|null $order
     * @param string $sort
     * @param int $mode
     * @return array|false
     */
    public function fetchAll(array $conditions, ?int $limit = null, ?string $order = null, string $sort = "ASC", int $mode = PDO::FETCH_BOTH): array|false
    {
        $where = static::parseWhere($conditions);
        $limit = !empty($limit) ? "LIMIT {$limit}" : "";
        $query = "SELECT * FROM `{$this->table}` WHERE {$where} {$limit}";
        if (is_string($order)) {
            $query .= " ORDER BY {$order} {$sort}";
        }
        $query .= ";";
        $statement = self::$db->query($query);
        return $statement?->fetchAll($mode);
    }
}