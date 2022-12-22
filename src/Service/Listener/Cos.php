<?php

namespace App\Service\Listener;

use App\Application;
use App\Model\AuthRecord;
use App\Model\Log;
use App\Model\Queue;
use App\Websocket\SocketContainerWrapper;
use Ratchet\Client\Connector;

class Cos extends AbstractListener
{
    private $objectToProcess = ['agent'];

    public static function getSystemName(): string
    {
        return 'cos';
    }

    public function prepareAuthData(array $result, AuthRecord|false $currentAuthRecord): AuthRecord|false
    {
        try {
            if (!empty($result['success'])) {
                if (!empty($currentAuthRecord)) {
                    $currentAuthRecord->token           = $result['response']['data']['accessToken'];
                    $currentAuthRecord->refresh_token   = $result['response']['data']['refreshToken'];
                } else {
                    $currentAuthRecord = new AuthRecord([
                        "system"        => "cos",
                        "token"         => $result['response']['data']['accessToken'],
                        "refresh_token" => $result['response']['data']['refreshToken'],
                    ]);
                }
                $currentAuthRecord->save();
            }
        } catch (\Exception $e) {
            $currentAuthRecord = false;
        }

        return $currentAuthRecord;
    }

    public function socket(): SocketContainerWrapper
    {
        try {
            $authRecord = $this->authentication->getAuthRecord();
            $token = $authRecord->token;
        } catch (\Exception $e) {
            $token = '';
        }
        $Address = "{$this->address}.>?Authorization=Bearer%20{$token}";

        $connector = new Connector();
        return new SocketContainerWrapper($connector($Address), $this);
    }

    /**
     * @throws \Exception
     */
    public function processMessage($message): void
    {
        $data = json_decode($message, true);
        $parsedContext = explode('.', $data['context']);
        if (in_array($parsedContext[1], $this->objectToProcess)) {
            $log = new Log([
                'info' => $data,
            ]);
            $log->save();

            if (isset($data['body']) && !empty($data['body']['action'])) {
                $action = $data['body']['action'];
                $systemNames = Application::getAllReceiverNames();
                $queueItem = new Queue([
                    'data' => [
                        'receivers' => $systemNames,
                        'object'    => $parsedContext[1],
                        'type'      => "{$parsedContext[1]}.{$action}",
                        'system'    => static::getSystemName(),
                        'data'      => $data['body']['data'],
                    ],
                    'priority' => 0,
                ]);
                $queueItem->save();
            }
        }
    }
}