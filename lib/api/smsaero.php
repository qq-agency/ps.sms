<?php

namespace Ps\Sms\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class SmsAero
{
    private $login;

    private $password;

    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    public function getSenderList()
    {
        $result = new Result();

        $senders = [];
        $response = $this->query('sign/list');

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());

            return $result;
        }

        $data = $response->getData();

        foreach ($data as $sender) {
            if (!isset($sender['extendStatus']) || $sender['extendStatus'] !== 'active') {
                continue;
            }
            $senders[] = [
                'id' => $sender['name'],
                'name' => $sender['name']
            ];
        }

        $result->setData($senders);

        return $result;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET)
    {
        $result = new Result();

        $http = new HttpClient();
        $http->setHeader('Accept', 'application/json');
        $http->setHeader('Content-Type', 'application/json');
        $http->setAuthorization($this->login, $this->password);
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query($httpMethod, 'https://gate.smsaero.ru/v2/'.$method.'?'.http_build_query($parameters));
        } else {
            $http->query($httpMethod, 'https://gate.smsaero.ru/v2/'.$method, $parameters);
        }

        try {
            $data = Json::decode($http->getResult());
            if (!$data['success']) {
                $result->addError(new Error($data['message']));

                return $result;
            }

            $result->setData($data['data']);
        } catch (ArgumentException $e) {
        }

        return $result;
    }

    public function send($parameters)
    {
        $result = new Result();

        $response = $this->query('sms/send', $parameters);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        return $result;
    }

    public function getBalance()
    {
        $result = new Result();

        $response = $this->query('balance');
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        $result->setData($response->getData());

        return $result;
    }
}
