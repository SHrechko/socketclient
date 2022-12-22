<?php

namespace App\Model;

use App\Service\AdapterWrapper;

class AuthRecord extends Entity {
    /** @var int */
    public $id;

    /** @var string */
    public $system;

    /** @var string */
    public $token;

    /** @var string */
    public $refresh_token;

    /** @var string */
    public $created;

    /** @var string */
    public $modified;

    public static function getTableName(): string
    {
        return 'clients_auth';
    }

    public static function getIdentifier(): string
    {
        return 'id';
    }
}