<?php

use App\Model\Queue;
use App\Service\Logger\Logger;

define('ROOT', dirname(__DIR__) . '/');

require_once ROOT . 'vendor/autoload.php';

$config = require ROOT . "config/config.php";

$queueProcess = new \App\Jobs\QueueProcess($config);
$logger = new Logger(ROOT . '/var/logs/cron', 'cron', true);

try {
    $dbConfig = $config['db'][$config['mode']];
    $db = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
    \App\Service\AdapterWrapper::init($db);

    $queues = Queue::getAllByConditions([
        "status" => 0,
        "`failed` < '{$queueProcess->errorLimit}'"
    ],
        10,
        'created'
    );
} catch (Exception $e) {
    $logger->log($e->getMessage() . "\r\n" . $e->getTraceAsString());
}

if (!empty($queues)) {
    foreach ($queues as $queueItem) {
        try {
            $queueProcess($queueItem);
        } catch (Exception $e) {
            $queueItem->failure_log = !empty($queueItem->failure_log) ? $queueItem->failure_log . "\r\n" . $e->getMessage() : $e->getMessage();
            $queueItem->save();
        }
    }
}

