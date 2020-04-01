<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Sender;
use Ps\Sms\Model\SenderCollection;

class SmsAero extends Base
{
    public function getSenderList()
    {
        $senderCollection = new SenderCollection();
        $data = $this->query('sign/list');

        foreach ($data['data'] as $sender) {
            if (!isset($sender['extendStatus']) || ($sender['extendStatus'] !== 'active')) {
                continue;
            }

            $senderCollection->append(new Sender($sender['name']));
        }

        return $senderCollection;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET)
    {
        $http = new HttpClient();
        $http->setHeader('Accept', 'application/json');
        $http->setHeader('Content-Type', 'application/json');
        $http->setAuthorization($this->login, $this->password);
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query($httpMethod, 'https://gate.smsaero.ru/v2/'.$method.'?'.http_build_query($parameters));
        } else {
            $http->query($httpMethod, 'https://gate.smsaero.ru/v2/'.$method, $parameters);
        }

        $data = Json::decode($http->getResult());
        if (!$data['success']) {
            throw new \RuntimeException($data['message']);
        }

        return $data;
    }

    public function getBalance()
    {
        $data = $this->query('balance');

        return new Balance($data['balance']);
    }

    // todo:
    public function send($parameters)
    {
        $result = new Result();

        $response = $this->query('sms/send', $parameters);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        return $result;
    }
}
