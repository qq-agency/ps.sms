<?php

namespace Ps\Sms\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class Intis
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

        $timestamp = $this->getTimestamp();
        $signature = [
            'timestamp' => $timestamp,
            'login' => $this->login,
        ];

        $senders = [];
        $response = $this->query(
            'senders',
            [
                'login' => $this->login,
                'signature' => $this->getSignature($signature),
                'timestamp' => $timestamp
            ]
        );

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());

            return $result;
        }

        $data = $response->getData();

        foreach ($data as $sender => $status) {
            if ($status !== 'completed') {
                continue;
            }
            $senders[] = [
                'id' => $sender,
                'name' => $sender
            ];
        }

        $result->setData($senders);

        return $result;
    }

    private function getTimestamp()
    {
        $http = new HttpClient();
        $http->query(HttpClient::HTTP_GET, 'https://new.sms16.ru/get/timestamp.php');

        return $http->getResult();
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET)
    {
        $result = new Result();

        $http = new HttpClient();
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query($httpMethod, 'https://new.sms16.ru/get/'.$method.'.php?'.http_build_query($parameters));
        } else {
            $http->query($httpMethod, 'https://new.sms16.ru/get/'.$method.'.php', $parameters);
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

    private function getSignature($parameters)
    {
        ksort($parameters);
        reset($parameters);

        return md5(implode($parameters).$this->password);
    }

    public function send($parameters)
    {
        $result = new Result();

        $timestamp = $this->getTimestamp();
        $signature = array_merge(
            $parameters,
            [
                'timestamp' => $timestamp,
                'login' => $this->login,
            ]
        );

        $parameters = array_merge(
            $parameters,
            [
                'login' => $this->login,
                'signature' => $this->getSignature($signature),
                'timestamp' => $timestamp
            ]
        );

        $response = $this->query('send', $parameters);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        return $result;
    }

    public function getBalance()
    {
        $result = new Result();

        $timestamp = $this->getTimestamp();
        $signature = [
            'timestamp' => $timestamp,
            'login' => $this->login,
        ];

        $parameters = [
            'login' => $this->login,
            'signature' => $this->getSignature($signature),
            'timestamp' => $timestamp
        ];

        $response = $this->query('balance', $parameters);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        $result->setData($response->getData());

        return $result;
    }
}
