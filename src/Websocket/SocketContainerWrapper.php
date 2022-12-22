<?php

namespace App\Websocket;

use App\Service\Listener\AbstractListener;
use App\Service\Trait\LoggerTrait;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class SocketContainerWrapper {

    use LoggerTrait;

    protected int $errorCount = 0;

    protected int $maxErrors = 25;

    public function __construct(protected Promise|PromiseInterface $container, protected AbstractListener $listener) {}

    public function processSocket() {
        $self = &$this;
        $this->container->then(function(WebSocket $conn) use (&$self) {
            $conn->on('message', function(MessageInterface $msg) use (&$self) {
                try {
                    $self->errorCount = 0;
                    $self->listener->processMessage($msg);
                } catch (\Exception $e) {
                    $self->writeToLog($e->getMessage().$e->getTraceAsString());
                }
            });

            $conn->on('close', function($code = null, $reason = null) use (&$self) {
                $self->listener->initAuthentication();
                $self->container = $self->listener->socket()->getSocketContainer();
                echo "Connection closed ({$code} - {$reason})\n";
            });

            $conn->send('OK');
        }, function(\Exception $e) use (&$self) {
            $self->errorCount += 1;
            if ($self->errorCount < $self->maxErrors) {
                $self->listener->initAuthentication();
                $self->container = $self->listener->socket()->getSocketContainer();
                $self->processSocket();
            }
            $self->writeToLog($e->getTraceAsString());
            echo "Could not connect: {$e->getMessage()}\n";
        });
    }

    public function getSocketContainer(): Promise|PromiseInterface {
        return $this->container;
    }
}