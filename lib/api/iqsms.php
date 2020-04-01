<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Sender;
use Ps\Sms\Model\SenderCollection;
use RuntimeException;

class IqSms extends Base
{
    public function getSenderList()
    {
        $senderCollection = new SenderCollection();

        $data = $this->query('senders');
        foreach ($data['senders'] as $sender) {
            if (!in_array($sender['status'], ['default', 'active'])) {
                continue;
            }
            $senderCollection->append(new Sender($sender['name']));
        }

        return $senderCollection;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET, $useJson = false)
    {
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

        $data = Json::decode($http->getResult());
        if ($data['status'] === 'error') {
            throw new RuntimeException($data['description']);
        }

        return $data;
    }

    public function getBalance()
    {
        $data = $this->query('balance', [], HttpClient::HTTP_POST, true);
        foreach ($data['balance'] as $item) {
            if ($item['type'] === 'RUB') {
                return new Balance($item['balance']);
            }
        }

        throw new RuntimeException('error');
    }

    // todo:
    public function send($parameters)
    {
        $result = new Result();

        $response = $this->query('send', $parameters, HttpClient::HTTP_POST);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        return $result;
    }
}
