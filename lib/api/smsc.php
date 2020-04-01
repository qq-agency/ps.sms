<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Sender;
use Ps\Sms\Model\SenderCollection;

class Smsc extends Base
{
    public function getSenderList()
    {
        $senderCollection = new SenderCollection();

        $data = $this->query('senders', ['get' => 1]);
        foreach ($data as $sender) {
            $senderCollection->append(new Sender($sender['sender']));
        }

        return $senderCollection;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_POST)
    {
        $parameters['login'] = $this->login;
        $parameters['psw'] = $this->password;
        $parameters['fmt'] = 3;

        $http = new HttpClient();
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query($httpMethod, 'https://smsc.ru/sys/'.$method.'.php?', http_build_query($parameters));
        } else {
            $http->query($httpMethod, 'https://smsc.ru/sys/'.$method.'.php', $parameters);
        }

        $data = Json::decode($http->getResult());
        if (isset($data['error'])) {
            throw new \RuntimeException($data['error']);
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

        $response = $this->query('send', $parameters);
        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        return $result;
    }
}
