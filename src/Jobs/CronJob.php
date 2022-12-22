<?php

namespace App\Jobs;

use App\Model\Queue;
use Exception;

abstract class CronJob
{
    public int $errorLimit = 5;

    public function __construct(protected array $config) {}

    public function __invoke(Queue $queueItem): void
    {
        $this->process($queueItem);
    }

    /**
     * @param Queue $queueItem
     * @return void
     * @throws Exception
     */
    protected abstract function process(Queue $queueItem): void;
}