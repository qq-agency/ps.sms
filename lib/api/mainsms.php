<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Sender;
use Ps\Sms\Model\SenderCollection;

class MainSMS extends Base
{
    public function getSenderList()
    {
        $signature = [
            'project' => $this->login,
        ];

        $senderCollection = new SenderCollection();
        $data = $this->query(
            'sender/list',
            [
                'project' => $this->login,
                'sign' => $this->getSignature($signature),
            ]
        );
        if (isset($data['senders'])) {
            foreach ($data['senders'] as $sender) {
                $senderCollection->append(new Sender($sender));
            }
        }

        return $senderCollection;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET)
    {
        $http = new HttpClient();
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query(
                $httpMethod,
                'https://mainsms.ru/api/mainsms/'.$method.'/?'.http_build_query($parameters)
            );
        } else {
            $http->query($httpMethod, 'https://mainsms.ru/api/mainsms/'.$method.'/', $parameters);
        }

        $data = Json::decode($http->getResult());
        if (isset($data['error'])) {
            throw new \RuntimeException($data['message']);
        }

        return $data;
    }

    private function getSignature($parameters)
    {
        ksort($parameters);

        return md5(sha1(implode(';', array_merge($parameters, [$this->password]))));
    }

    public function getBalance()
    {
        $signature = [
            'project' => $this->login,
        ];

        $parameters = [
            'project' => $this->login,
            'sign' => $this->getSignature($signature),
        ];

        $data = $this->query('message/balance', $parameters);

        return new Balance($data['balance']);
    }

    // todo:
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
}
