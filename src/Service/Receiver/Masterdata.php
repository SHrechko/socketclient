<?php

namespace App\Service\Receiver;

use App\Model\Agent;
use App\Model\AuthRecord;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Client\ClientExceptionInterface;

class Masterdata extends AbstractReceiver implements AgentProcessingInterface
{
    protected string $domain = '';

    public static function getSystemName(): string {
        return 'masterdata';
    }

    protected function getDefaultHeaders(): array
    {
        return [];
    }

    public function addClient(Agent $client): Agent|false
    {
        try {
            //TODO:: change to $this->client->sendRequest($uri, ...etc)
            /** @var Agent|false $result */
            $result = false;
            $preparedClient = $client->toArray();
            $guzzleClient = new \GuzzleHttp\Client();
            $request = new \GuzzleHttp\Psr7\Request(
                'POST',
                "{$this->domain}/api2/client/create",
                ['Content-Type' => 'application/json'], json_encode($preparedClient)
            );
            $response = $guzzleClient->sendRequest($request);
            $body = json_decode($response->getBody(), true);
            $reason = $response->getReasonPhrase();

            if (!is_array($body) && empty($reason)) {
                throw new \Exception('Invalid response body');
            } else {
                $statusCode = $response->getStatusCode();
                if (200 <= $statusCode && 300 > $statusCode) {
                    $result = $client;
                }
            }
        } catch (\Exception|ClientExceptionInterface $e) {
        }

        return $result;
    }

    public function prepareAuthData(array $result, AuthRecord|false $currentAuthRecord): AuthRecord|false
    {
//        $currentAuthRecord = $this->authentication->getAuthRecord(static::getSystemName());
//        return $this->authentication->authenticate(function($result) use ($currentAuthRecord) {
//            try {
//                if (!empty($result['success'])) {
//                    if (!empty($currentAuthRecord)) {
//                        $currentAuthRecord->token = $result['response']['data']['accessToken'];
//                        $currentAuthRecord->refresh_token = $result['response']['data']['refreshToken'];
//                    } else {
//                        $currentAuthRecord = new AuthRecord([
//                            "system" => "cos",
//                            "token" => $result['response']['data']['accessToken'],
//                            "refresh_token" => $result['response']['data']['refreshToken'],
//                        ]);
//                    }
//                    $currentAuthRecord->save();
//                }
//            } catch (\Exception $e) {
//                $currentAuthRecord = false;
//            }
//            return $currentAuthRecord;
//        });
        return false;
    }

    public function createAgent(Agent $agent): false|PromiseInterface
    {
        $preparedAgent = $this->prepareAgent($agent);
        return $this->client->sendAsync($this->client::METHOD_POST, "{$this->domain}/agent/create", $preparedAgent, $this->getDefaultHeaders());
    }

    public function updateAgent(Agent $agent): false|PromiseInterface
    {
        $preparedAgent = $this->prepareAgent($agent);
        return $this->client->sendAsync($this->client::METHOD_PUT, "{$this->domain}/agent/update", $preparedAgent, $this->getDefaultHeaders());
    }

    public function prepareAgent(Agent $agent): array
    {
        return [];
    }
}