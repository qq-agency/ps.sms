<?php

namespace Ps\Sms\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class MainSMS
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

        $signature = [
            'project' => $this->login,
        ];

        $senders = [];
        $response = $this->query(
            'sender/list',
            [
                'project' => $this->login,
                'sign' => $this->getSignature($signature),
            ]
        );

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());

            return $result;
        }

        $data = $response->getData();

        if (isset($data['senders'])) {
            foreach ($data['senders'] as $sender) {
                $senders[] = [
                    'id' => $sender,
                    'name' => $sender
                ];
            }
        }

        $result->setData($senders);

        return $result;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET)
    {
        $result = new Result();

        $http = new HttpClient();
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query(
                $httpMethod,
                'https://mainsms.ru/api/mainsms/'.$method.'/?'.http_build_query($parameters)
            );
        } else {
            $http->query($httpMethod, 'https://mainsms.ru/api/mainsms/'.$method.'/', $parameters);
        }

        try {
            $data = Json::decode($http->getResult());
            if (isset($data['error'])) {
                $result->addError(new Error($data['message']));

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

        return md5(sha1(implode(';', array_merge($parameters, [$this->password]))));
    }

    public function send($parameters)
    {
        $result = new Result();

        $signature = array_merge(
            $parameters,
            [
                'project' => $this->login,
            ]
        );

        $parameters = array_merge(
            $parameters,
            [
                'project' => $this->login,
                'sign' => $this->getSignature($signature),
            ]
        );

        $response = $this->query('message/send', $parameters);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        return $result;
    }

    public function getBalance()
    {
        $result = new Result();

        $signature = [
            'project' => $this->login,
        ];

        $parameters = [
            'project' => $this->login,
            'sign' => $this->getSignature($signature),
        ];

        $response = $this->query('message/balance', $parameters);

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        $result->setData($response->getData());

        return $result;
    }
}
