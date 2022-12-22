<?php

namespace App\Jobs;

use App\AbstractSystem;
use App\Application;
use App\Model\Agent;
use App\Model\Queue;
use App\Service\Receiver\AgentProcessingInterface;
use GuzzleHttp\Promise\Utils;

class QueueProcess extends CronJob
{
    /**
     * @inheritDoc
     */
    protected function process(Queue $queueItem): void
    {
        $receivers = Application::getAllReceiversObjects($this->config);
        $taskInfo = $queueItem->data;
        if (count($taskInfo['receivers']) > 0) {
            $receivers = array_filter($receivers, fn($systemName) => in_array($systemName, $taskInfo['receivers']));
            $promises = [];
            $failedSystems = [];

            switch ($taskInfo['object']) {
                case 'agent':
                    $agent = new Agent($taskInfo['data']);
                    switch ($taskInfo['type']) {
                        case 'agent.created':
                            foreach ($receivers as $receiver) {
                                if ($receiver instanceof AgentProcessingInterface) {
                                    $systemName = $receiver::getSystemName();
                                    try {
                                        $request = $receiver->createAgent($agent);
                                        if (!empty($request)) {
                                            $promises[$systemName] = $request;
                                        } else {
                                            $failedSystems[] = $systemName;
                                        }
                                    } catch (\Exception $e) {
                                        $failedSystems[] = $systemName;
                                        $queueItem->failure_log = !empty($queueItem->failure_log) ? $queueItem->failure_log . '\n' . $e->getMessage() : $e->getMessage();
                                    }
                                }
                            }
                            break;
                        case 'agent.updated':
                            foreach ($receivers as $receiver) {
                                if ($receiver instanceof AgentProcessingInterface) {
                                    $systemName = $receiver::getSystemName();
                                    try {
                                        $request = $receiver->updateAgent($agent);
                                        if (!empty($request)) {
                                            $promises[$systemName] = $request;
                                        } else {
                                            $failedSystems[] = $systemName;
                                        }
                                    } catch (\Exception $e) {
                                        $failedSystems[] = $systemName;
                                        $queueItem->failure_log = !empty($queueItem->failure_log) ? $queueItem->failure_log . "\n" . $e->getMessage() : $e->getMessage();
                                    }
                                }
                            }
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
            $responses = Utils::settle($promises)->wait();

            foreach ($responses as $systemName => $response) {
                if ($response['state'] !== 'fulfilled') {
                    $failedSystems[] = $systemName;
                }
            }

            if (count($failedSystems) > 0) {
                $queueItem->failed = $queueItem->failed + 1;
                $queueItem->data['receivers'] = $failedSystems;
                $strFailedSystems = implode(", ", $failedSystems);
                $error = "Not all systems received data (failed systems: {$strFailedSystems})";
                $queueItem->failure_log = !empty($queueItem->failure_log) ? $queueItem->failure_log . "\n" . $error : $error;
                $queueItem->save();
            } else {
                $queueItem->delete();
            }
        }
    }
}