<?php

namespace App\Service;


use App\ClientWrapper;
use App\Model\AuthRecord;
use Psr\Http\Client\ClientExceptionInterface;

class Authentication {
    /** @var ClientWrapper */
    private ClientWrapper $client;

    private ?AuthRecord $authRecord = null;

    public function __construct(private string $uri, private array $options, private array $headers = [])
    {
        $this->client = new ClientWrapper();
    }

    /**
     * if you provide callback function, it must return the AuthRecord object
     *
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function authenticate(): AuthRecord|array
    {
        return $this->client->sendRequest(ClientWrapper::METHOD_POST, $this->uri, $this->options, $this->headers);
    }

    public function setAuthRecord(AuthRecord|null $authRecord): void {
        $this->authRecord = $authRecord;
    }

    /**
     * @throws \Exception
     */
    public function getAuthRecord(?string $systemName = null): AuthRecord|false {
        if (!empty($this->authRecord)) {
            return $this->authRecord;
        }

        $authRecord = false;
        if (!empty($systemName)) {
            $authRecord = AuthRecord::getOneByConditions(['system' => $systemName]);
        }

        return $authRecord;
    }
}
