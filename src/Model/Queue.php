<?php

namespace App\Model;

use App\Service\AdapterWrapper;

class Queue extends Entity
{
    /** @var int */
    public $id;

    /** @var string */
    public $created;

    /** @var int */
    public $priority;

    /** @var string */
    public $last_attempted;

    /** @var array */
    public $data;

    /** @var int */
    public $failed;

    /** @var string */
    public $failure_log;

    public static function getTableName(): string {
        return 'queue';
    }

    public static function getIdentifier(): string {
        return 'id';
    }

    protected function onConstruct($arrAttr): array
    {
        if (!is_array($arrAttr['data'])) {
            $arrAttr['data'] = json_decode($arrAttr['data'], true);
        }
        return $arrAttr;
    }

    protected function beforeInsert($arrAttr): array
    {
        if (is_array($arrAttr['data'])) {
            $arrAttr['data'] = json_encode($arrAttr['data']);
        }
        return $arrAttr;
    }
    protected function beforeUpdate($arrAttr): array
    {
        if (is_array($arrAttr['data'])) {
            $arrAttr['data'] = json_encode($arrAttr['data']);
        }
        return $arrAttr;
    }
}