<?php

namespace Ps\Sms\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class IqSms
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
        $response = $this->query('senders');

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());

            return $result;
        }

        $data = $response->getData();

        foreach ($data['senders'] as $sender) {
            if (!in_array($sender['status'], ['default', 'active'])) {
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

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET, $useJson = false)
    {
        $result = new Result();

        $http = new HttpClient();
        $http->setAuthorization($this->login, $this->password);
        $http->setHeader('Accept', 'application/json');
        $http->setHeader('Content-Type', 'application/json');
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query(
                $httpMethod,
                'http://api.iqsms.ru/messages/v2/'.$method.($useJson ? '.json?' : '/?').http_build_query($parameters)
            );
        } else {
            $http->query(
                $httpMethod,
                'http://api.iqsms.ru/messages/v2/'.$method.($useJson ? '.json' : '/'),
                json_encode($parameters)
            );
        }

        try {
            $data = Json::decode($http->getResult());
            if ($data['status'] === 'error') {
                $result->addError(new Error($data['description']));

                return $result;
            }

            $result->setData($data);
        } catch (ArgumentException $e) {
        }

        return $result;
    }

    public function send($parameters)
    {
        $result = new Result();

        $response = $this->query('send', $parameters, HttpClient::HTTP_POST);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        return $result;
    }

    public function getBalance()
    {
        $result = new Result();

        $response = $this->query('balance', [], HttpClient::HTTP_POST, true);

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        $result->setData($response->getData());

        return $result;
    }
}
