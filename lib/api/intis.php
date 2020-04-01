<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Sender;
use Ps\Sms\Model\SenderCollection;
use RuntimeException;

class Intis extends Base
{
    public function getSenderList()
    {
        $timestamp = $this->getTimestamp();
        $signature = [
            'timestamp' => $timestamp,
            'login' => $this->login,
        ];

        $data = $this->query(
            'senders',
            [
                'login' => $this->login,
                'signature' => $this->getSignature($signature),
                'timestamp' => $timestamp
            ]
        );

        $senderCollection = new SenderCollection();
        foreach ($data as $sender => $status) {
            if ($status !== 'completed') {
                continue;
            }

            $senderCollection->append(new Sender($sender));
        }

        return $senderCollection;
    }

    private function getTimestamp()
    {
        $http = new HttpClient();
        $http->query(HttpClient::HTTP_GET, 'https://new.sms16.ru/get/timestamp.php');

        return $http->getResult();
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET)
    {
        $http = new HttpClient();
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query($httpMethod, 'https://new.sms16.ru/get/'.$method.'.php?'.http_build_query($parameters));
        } else {
            $http->query($httpMethod, 'https://new.sms16.ru/get/'.$method.'.php', $parameters);
        }

        $data = Json::decode($http->getResult());
        if (isset($data['error'])) {
            throw new RuntimeException($data['error']);
        }

        return $data;
    }

    private function getSignature($parameters)
    {
        ksort($parameters);
        reset($parameters);

        return md5(implode($parameters).$this->password);
    }

    public function getBalance()
    {
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

        $data = $this->query('balance', $parameters);

        return new Balance($data['money']);
    }

    public function send($message)
    {
        $data = [
            'phones' => $message->getPhone(),
            'mes' => $message->getText(),
            'sender' => $message->getSender()
        ];

        $timestamp = $this->getTimestamp();
        $signature = array_merge(
            $data,
            [
                'timestamp' => $timestamp,
                'login' => $this->login,
            ]
        );

        $parameters = array_merge(
            $data,
            [
                'login' => $this->login,
                'signature' => $this->getSignature($signature),
                'timestamp' => $timestamp
            ]
        );

        return $this->query('send', $parameters, HttpClient::HTTP_POST);
    }
}
