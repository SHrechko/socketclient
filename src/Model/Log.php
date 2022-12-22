<?php

namespace App\Model;

use App\Service\AdapterWrapper;

class Log extends Entity {
    /** @var int */
    public $id;

    /** @var array */
    public $info;

    /** @var string */
    public $created;

    public static function getTableName(): string
    {
        return 'logs';
    }

    public static function getIdentifier(): string
    {
        return 'id';
    }

    protected function onConstruct($arrAttr): array
    {
        if (!is_array($arrAttr['info'])) {
            $arrAttr['info'] = json_decode($arrAttr['info'], true);
        }
        return $arrAttr;
    }

    protected function beforeInsert($arrAttr): array
    {
        if (is_array($arrAttr['info'])) {
            $arrAttr['info'] = json_encode($arrAttr['info']);
        }
        return $arrAttr;
    }

    protected function beforeUpdate($arrAttr): array
    {
        if (is_array($arrAttr['info'])) {
            $arrAttr['info'] = json_encode($arrAttr['info']);
        }
        return $arrAttr;
    }
}