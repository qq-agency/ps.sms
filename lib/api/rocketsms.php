<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Sender;
use Ps\Sms\Model\SenderCollection;

class RocketSMS extends Base
{
    public function getSenderList()
    {
        $senderCollection = new SenderCollection();

        $data = $this->query('senders');
        if (isset($data)) {
            foreach ($data as $sender) {
                if (!$sender['verified'] || !$sender['checked'] || !$sender['registered']) {
                    continue;
                }

                $senderCollection->append(new Sender($sender['sender']));
            }
        }

        return $senderCollection;
    }

    private function query($method, $parameters = [], $httpMethod = HttpClient::HTTP_GET)
    {
        $parameters = array_merge(['username' => $this->login, 'password' => md5($this->password)], $parameters);

        $http = new HttpClient();
        if ($httpMethod === HttpClient::HTTP_GET) {
            $http->query(
                $httpMethod,
                'https://api.rocketsms.by/simple/'.$method.'?'.http_build_query($parameters)
            );
        } else {
            $http->query($httpMethod, 'https://api.rocketsms.by/simple/'.$method, $parameters);
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
