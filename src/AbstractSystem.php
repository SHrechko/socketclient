<?php

namespace App;

use App\Model\AuthRecord;
use App\Service\Authentication;
use App\Service\Logger\Logger;

abstract class AbstractSystem implements SystemInterface
{
    /** @var ClientWrapper */
    protected ClientWrapper $client;

    protected Logger $logger;

    protected Authentication $authentication;

    /**
     * @throws \Exception|\Psr\Http\Client\ClientExceptionInterface
     */
    public function __construct($config)
    {
        $this->logger = new Logger(ROOT . '/var/logs/' . $this->getSystemName(), $this->getSystemName(), true);
        $this->client = new ClientWrapper();
        $this->authentication = new Authentication($config['authentication']['authApiUri'], $config['authentication']['options']);
        try {
            $authRecord = $this->authentication->getAuthRecord(static::getSystemName());
            if (empty($authRecord)) {
                $this->initAuthentication();
            } else {
                $this->authentication->setAuthRecord($authRecord);
            }
        } catch (\Exception|\Psr\Http\Client\ClientExceptionInterface $e) {
            $this->logger->log($e->getTraceAsString());
        }
    }
    abstract protected function prepareAuthData(array $result, AuthRecord|false $currentAuthRecord): AuthRecord|false;

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Exception
     */
    public function initAuthentication(): void
    {
            $result = $this->authentication->authenticate();
            $authRecord = $this->authentication->getAuthRecord(static::getSystemName());
            $authRecord = $this->prepareAuthData($result, $authRecord);
            $this->authentication->setAuthRecord(!empty($authRecord) ? $authRecord : null);
    }
}