<?php

namespace App;

use App\Service\Listener\AbstractListener;
use App\Service\Logger\Logger;
use App\Service\Receiver\Masterdata;
use App\Service\Listener\Cos;
use App\Websocket\SocketContainerWrapper;
use Exception;
use Ratchet\Client\WebSocket;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;

class Application
{
    /** @var Array<AbstractListener> $listeners */
    private array $listeners = [];

    private function __construct() {}

    public static function init(array $config): static {
        $listeners = [
            new Cos($config['client_services']['cos']),
        ];

        $application = new static();
        $application->setListeners($listeners);

//      $receivers = static::getAllReceivers($config);
        return $application;
    }

    /**
     * @param AbstractListener[] $listeners
     *
     * @throws Exception
     */
    public function setListeners(array $listeners): void {
        foreach ($listeners as $listener) {
            if ($listener instanceof AbstractListener) {
                $this->listeners[$listener->getSystemName()] = $listener;
            } else {
                throw new Exception('Trying to set not AbstractListener object');
            }
        }
    }

    /**
     * @return AbstractListener[]
     */
    public function getListeners(): array {
        return $this->listeners;
    }

    public function run(): void {
        // get a list of all the listeners that will be connected to us..
        // add the listening socket to this list
        $listeners = $this->getListeners();
        /** @var SocketContainerWrapper[] $sockets */
        $sockets = [];
        foreach ($listeners as $systemName => $listener) {
            //creating array of sockets
            $sockets[$systemName] = $listener->socket();
            $sockets[$systemName]->processSocket();
        }
//        while (true) {
            // create a copy, so $clients doesn't get modified by socket_select()
//            $read = [];

            // get a list of all the clients that have data to be read from
            // if there are no clients with data, go to next iteration
//            $except = $write = NULL;
//            if (socket_select($read, $write, $except, 0) < 1)
//                continue;

            // check if there is a client trying to connect
//            $sock =;
//            if (in_array($sock, $read)) {
//                // accept the client, and add him to the $clients array
//                $clients[] = $newsock = socket_accept($sock);
//
//                // send the client a welcome message
//                socket_write($newsock, "1");
//
//                socket_getpeername($newsock, $ip);
//                echo "New client connected: {$ip}\n";
//
//                // remove the listening socket from the clients-with-data array
//                $key = array_search($sock, $read);
//                unset($read[$key]);
//            }

            // loop through all the clients that have data to read from
//            foreach ($read as $socketKey => $read_sock) {
//                // read until newline or 1024 bytes
//                // socket_read while show errors when the client is disconnected, so silence the error messages
//                $jsonString = "";
//                if (false === @socket_recv($read_sock, $jsonString, 2048, MSG_WAITALL)) {
//                    $this->errorsCounter[$socketKey] += 1;
//                    $err = socket_strerror(socket_last_error($read_sock));
//                    $logger->log($err);
//                    $listeners[$socketKey]->initAuthentication();
//                    $sockets[$socketKey] = $listeners[$socketKey]->socket();
//                    $jsonString = '';
//                    $readedSocket = $sockets[$socketKey];
//                    if (false !== @socket_recv($readedSocket, $jsonString, 2048, MSG_WAITALL)) {
//                        $this->errorsCounter[$socketKey] += 1;
//                        $err = socket_strerror(socket_last_error($readedSocket));
//                        $logger->log($err);
//                        continue;
//                    }
//                }
//
//                if (!empty($this->errorsCounter[$socketKey]))
//                // trim off the trailing/beginning white spaces
//                $data = trim($jsonString);
//                $logger->log($data ?? 'fail to reading');

//                // check if there is any data after trimming off the spaces
//                if (!empty($data)) {
//
//                    // send this to all the clients in the $clients array (except the first one, which is a listening socket)
//                    foreach ($clients as $send_sock) {
//
//                        // if its the listening sock or the client that we got the message from, go to the next one in the list
//                        if (
//                            $send_sock == $sock ||
//                            $send_sock == $read_sock
//                        )
//                            continue;
//
//                        // write the message to the client -- add a newline character to the end of the message
//                        socket_write($send_sock, $data."\n");
//
//                    } // end of broadcast foreach
//
//                }

//            } // end of reading foreach
//        }

        // close the listening socket
//        foreach ($sockets as $socket) {
//            if ($socket) {
//                socket_close($socket);
//            }
//        }

//        while(true) {
//            foreach ($sockets as $systemName => $socket) {
//                try {
//                    $msg = $socket->readSocket(); // read will wait for data
//
//                    $logger->log($msg);
//                } catch (\Exception $e) {
//                    ob_flush();
//                    flush();
//                    $logger->log($e->getTraceAsString());
//                    if (empty($this->errorsCounter[$systemName])) {
//                        $this->errorsCounter[$systemName] = 0;
//                    }
//                    $this->errorsCounter[$systemName] += 1;
//                    try {
//                        $this->listeners[$systemName]->initAuthentication();
//                        $sockets[$systemName] = $this->listeners[$systemName]->socket();
//                        $msg = $socket->readSocket(); // read will wait for data
//                        $logger->log($msg);
//                        ob_flush();
//                        flush();
//                    } catch (\Exception|ClientExceptionInterface $reconnectException) {
//                        ob_flush();
//                        flush();
//                        $logger->log($reconnectException->getTraceAsString());
//                        $this->errorsCounter[$systemName] += 1;
//                    }
//                }
//            }
//        }
    }

    /**
     * @param array $config
     * @return AbstractSystem[]
     * @throws Exception
     */
    public static function getAllReceiversObjects(array $config): array {
        $receivers = static::getAllReceivers();
        $receiverObjects = [];
        foreach ($receivers as $receiverClass) {
            if (is_a($receiverClass, AbstractSystem::class, true)) {
                $receiverObjects[$receiverClass::getSystemName()] = new $receiverClass($config[Masterdata::getSystemName()]);
            }
        }
        return $receiverObjects;
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public static function getAllReceivers(): array {
        return [
            Masterdata::class,
        ];
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public static function getAllReceiverNames(): array {
        $receivers = static::getAllReceivers();
        $systemNames = [];
        foreach ($receivers as $receiverClass) {
            if (is_a($receiverClass, AbstractSystem::class, true)) {
                $systemNames[] = $receiverClass::getSystemName();
            }
        }
        return $systemNames;
    }
}