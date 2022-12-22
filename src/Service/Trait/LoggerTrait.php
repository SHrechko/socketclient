<?php

namespace App\Service\Trait;

use App\Service\Logger\Logger;

trait LoggerTrait {

    public function writeToLog($message) {
        $logger = new Logger(ROOT . '/var/logs/socketread', 'main', true);
        $logger->log($message);
    }
}