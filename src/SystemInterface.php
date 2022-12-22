<?php

namespace App;

interface SystemInterface {

    /**
     * Get name of system
     *
     * @return string
     */
    public static function getSystemName(): string;

}