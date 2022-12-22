<?php

define('ROOT', dirname(__DIR__) . '/');

require_once ROOT . 'vendor/autoload.php';

$config = require ROOT . "config/config.php";

try {
    $dbConfig = $config['db'][$config['mode']];
    $db = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
    \App\Service\AdapterWrapper::init($db);
//
//    $statement = $db->query("SELECT * FROM `clients_auth` WHERE `system` = 'cos';");
//    $clientsAuth = $statement?->fetch(PDO::FETCH_ASSOC);
//
//    $systemAuthConfig = $config['client_services']['cos']['authentication'];
//    $client = new \GuzzleHttp\Client();
//    $request = new \GuzzleHttp\Psr7\Request('POST', $systemAuthConfig['authApiUri'], ['Content-Type' => 'application/json'], json_encode($systemAuthConfig['options']));
//    $response = $client->sendRequest($request);
//    $body = json_decode($response->getBody(), true);
//    $reason = $response->getReasonPhrase();
//    $result = [
//        'response' => [],
//        'success' => false,
//        'status_code' => null,
//    ];
//
//    if (!is_array($body) && empty($reason)) {
//        throw new \Exception('Invalid response body');
//    } else {
//        $result['status_code'] = $response->getStatusCode();
//        if (200 <= $result['status_code'] && 300 > $result['status_code']) {
//            $result['success'] = true;
//        }
//        $result['response'] = is_array($body) ? $body : $reason;
//
//        if (!empty($result['success'])) {
//            if (!empty($clientsAuth)) {
//                $query = "UPDATE `clients_auth`
//                SET `token` = '{$result['response']['data']['accessToken']}', `refresh_token` = '{$result['response']['data']['refreshToken']}' WHERE id = '{$clientsAuth['id']}' ;";
//            } else {
//                $query = "INSERT INTO `clients_auth` (`system`, `token`, `refresh_token`)
//                values ('cos', '{$result['response']['data']['accessToken']}', '{$result['response']['data']['refreshToken']}');";
//            }
//            $insertResult = (new \App\Service\AdapterWrapper())->execute($query);
//        }
//    }
    $application = \App\Application::init($config);
    $application->run();

} catch (\Psr\Http\Client\ClientExceptionInterface|Exception $e) {
    var_dump($e);
}
