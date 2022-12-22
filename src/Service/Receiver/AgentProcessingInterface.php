<?php

namespace App\Service\Receiver;

use App\Model\Agent;
use GuzzleHttp\Promise\PromiseInterface;

interface AgentProcessingInterface
{
    public function createAgent(Agent $agent): false|PromiseInterface;

    public function updateAgent(Agent $agent): false|PromiseInterface;

    public function prepareAgent(Agent $agent): array;
}