<?php

namespace App\Model;

use App\Service\AdapterWrapper;
use Exception;
use PDOException;

abstract class Entity extends Attributable
{
    public function __construct(array $attrs = [])
    {
        $attrs = $this->onConstruct($attrs);
        parent::__construct($attrs);
    }

    protected function onConstruct(array $arrAttr): array {
        return $arrAttr;
    }

    protected function beforeInsert(array $arrAttr): array {
        return $arrAttr;
    }

    protected function beforeUpdate(array $arrAttr): array {
        return $arrAttr;
    }

    public abstract static function getTableName(): string;

    public abstract static function getIdentifier(): string;

    /**
     * @param $id
     * @return false|static
     * @throws Exception
     */
    public static function getByIdentifier($id): false|static {
        $identifier = static::getIdentifier();
        return static::getOneByConditions([$identifier => $id]);
    }

    /**
     * @param array $conditions
     * @return bool|static
     * @throws Exception
     */
    public static function getOneByConditions(array $conditions): bool|static
    {
        $result = (new AdapterWrapper(static::getTableName()))->fetch($conditions, \PDO::FETCH_ASSOC);
        return !empty($result) ? new static($result) : false;
    }

    /**
     * @param array $conditions
     * @param int|null $limit
     * @param string|null $order
     * @param string $sort
     * @return false|static[]
     */
    public static function getAllByConditions(array $conditions, ?int $limit = null, ?string $order = null, string $sort = "ASC")
    {
        if ($order === null) {
            $order = static::getIdentifier();
        }
        $tableName = static::getTableName();
        $result = (new AdapterWrapper($tableName))->fetchAll($conditions, $limit, $order, $sort, \PDO::FETCH_ASSOC);
        $entities = [];
        if (!empty($result)) {
            $staticClass = static::class;
            foreach ($result as $item) {
                $entities[] = $staticClass($item);
            }
        } else {
            $entities = false;
        }

        return $entities;
    }

    /**
     * @return $this
     * @throws Exception|PDOException
     */
    public function save(): static {
        $data = $this->toArray();
        $tableName = static::getTableName();
        $identifier = static::getIdentifier();
        $id = null;

        if (!empty($this->$identifier)) {
            $data = $this->beforeUpdate($data);
            $result = (new AdapterWrapper($tableName))->update($data, [$identifier => $this->$identifier]);
            if (!empty($result)) {
                $id = $this->$identifier;
            }
        } else {
            $data = $this->beforeInsert($data);
            $result = (new AdapterWrapper($tableName))->insert($data);
            if (!empty($result)) {
                $id = $result;
            }
        }
        $result = static::getByIdentifier($id);
        if (!empty($result)) {
            $this->setAttributes($result->toArray());
        }
        return $this;
    }

    /**
     * @throws PDOException
     * @return void
     */
    public function delete(): void {
        $tableName = static::getTableName();
        $identifier = static::getIdentifier();
        if (!empty($this->$identifier)) {
            (new AdapterWrapper($tableName))->delete([$identifier => $this->$identifier]);
        }
    }
}