<?php


namespace App;


use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class NylasAuthentication
 * @package Officio\Calendar\Service
 */
class ClientWrapper
{

    public const METHOD_OPTIONS  = 'OPTIONS';
    public const METHOD_GET      = 'GET';
    public const METHOD_HEAD     = 'HEAD';
    public const METHOD_POST     = 'POST';
    public const METHOD_PUT      = 'PUT';
    public const METHOD_DELETE   = 'DELETE';
    public const METHOD_TRACE    = 'TRACE';
    public const METHOD_CONNECT  = 'CONNECT';
    public const METHOD_PATCH    = 'PATCH';
    public const METHOD_PROPFIND = 'PROPFIND';

    /**
     * @return array
     */
    protected function getDefaultHeaders(): array {
        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];
    }

    /**
     * @return array of all Request::METHOD_* constant-fields
     */
    private function getAllMethodTypes(): array {
        return [
            self::METHOD_OPTIONS,
            self::METHOD_GET,
            self::METHOD_HEAD,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE,
            self::METHOD_TRACE,
            self::METHOD_CONNECT,
            self::METHOD_PATCH,
            self::METHOD_PROPFIND
        ];
    }


    /**
     * Sends request
     * @param string $uri
     * @param array $params
     * @param string $method must be one of Request::METHOD_* constant-fields
     * @return false|array
     * @throws \Exception|ClientExceptionInterface
     * TODO Handle non-200 responses better
     * TODO Use Guzzle HTTP client here instead?
     */
    public function sendRequest(string $method, string $uri, array $params = [], ?array $headers = []) {
        if (!in_array($method, $this->getAllMethodTypes())) {
            return false;
        }

        $result = [
            'response' => [],
            'status_code' => null,
            'success' => false,
        ];

        $httpClient = new GuzzleClient();
        $headers    = array_merge($this->getDefaultHeaders(), $headers);
        $request = new GuzzleRequest($method, "https://{$uri}", $headers, json_encode($params));

        $response = $httpClient->sendRequest($request);

        $body   = json_decode($response->getBody(), true);
        $reason = $response->getReasonPhrase();

        if (!is_array($body) && empty($reason)) {
            throw new \Exception('Invalid response body');
        } else {
            $result['status_code'] = $response->getStatusCode();
            if (200 <= $result['status_code'] && 300 > $result['status_code']) {
                $result['response'] = is_array($body) ? $body : $reason;
                $result['success'] = true;
            } else {
                $result['error'] = $body['message'];
            }
        }


        return $result;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param array|null $headers
     * @return false|PromiseInterface
     */
    public function sendAsync(string $method, string $uri, array $params = [], ?array $headers = []) {
        if (!in_array($method, $this->getAllMethodTypes())) {
            return false;
        }

        $headers = array_merge($this->getDefaultHeaders(), $headers);

        $body    = json_encode($params);
        $request = new GuzzleRequest($method, "https://{$uri}", $headers, $body);
        $client  = new GuzzleClient();
        return $client->sendAsync($request);
    }
}