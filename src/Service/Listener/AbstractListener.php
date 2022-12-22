<?php

namespace App\Service\Listener;

use App\AbstractSystem;
use App\Model\AuthRecord;
use App\Websocket\SocketContainerWrapper;
use App\Websocket\WebsocketCore;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

abstract class AbstractListener extends AbstractSystem
{
    protected string $address;

    /**
     * @throws \Exception|\Psr\Http\Client\ClientExceptionInterface
     */
    public function __construct($config)
    {
        $this->address = $config['socket']['address'];
        parent::__construct($config);
    }

    abstract public function socket(): SocketContainerWrapper;

    abstract public function processMessage($message): void;
}