<?php

namespace Ps\Sms\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class Smsc
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
        $response = $this->query('senders', ['get' => 1]);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());

            return $result;
        }

        $data = $response->getData();
        foreach ($data as $sender) {
            $senders[] = [
                'id' => $sender['sender'],
                'name' => $sender['sender']
            ];
        }

        $result->setData($senders);

        return $result;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_POST)
    {
        $result = new Result();

        $parameters['login'] = $this->login;
        $parameters['psw'] = $this->password;
        $parameters['fmt'] = 3;

        $http = new HttpClient();
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query($httpMethod, 'https://smsc.ru/sys/'.$method.'.php?', http_build_query($parameters));
        } else {
            $http->query($httpMethod, 'https://smsc.ru/sys/'.$method.'.php', $parameters);
        }

        try {
            $data = Json::decode($http->getResult());
            if (isset($data['error'])) {
                $result->addError(new Error($data['error']));

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

        $response = $this->query('send', $parameters);
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
