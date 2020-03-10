<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

class TurboSMS
{
    private $login;

    private $password;

    private $client;

    public function __construct($login, $password)
    {
        $this->login = $login;

        $this->password = $password;

        try {
            $context = stream_context_create(
                [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]
            );

            $this->client = new \SoapClient(
                'https://turbosms.in.ua/api/wsdl.html',
                [
                    'exceptions' => true,
                    'stream_context' => $context
                ]
            );
        } catch (\SoapFault $e) {
        }
    }

    public function send($parameters)
    {
        $result = new Result();
        $auth = $this->auth();

        if (!$auth->isSuccess()) {
            $result->addErrors($auth->getErrors());
        }

        $response = $this->client->SendSMS($parameters);
        $message = $response->SendSMSResult->ResultArray[0];
        if ($message !== Loc::getMessage('PS_SMS_TURBOSMS_SEND_SUCCESS_MESSAGE')) {
            $result->addError(new Error($message));
        }

        return $result;
    }

    private function auth()
    {
        $result = new Result();

        $response = $this->client->Auth(
            [
                'login' => $this->login,
                'password' => $this->password
            ]
        );

        if ($response->AuthResult !== Loc::getMessage('PS_SMS_TURBOSMS_AUTH_SUCCESS_MESSAGE')) {
            $result->addError(new Error($response->AuthResult));
        }

        return $result;
    }

    public function getBalance()
    {
        $result = new Result();
        $auth = $this->auth();

        if (!$auth->isSuccess()) {
            $result->addErrors($auth->getErrors());
        }

        $response = $this->client->GetCreditBalance();
        $balance = $response->GetCreditBalanceResult;

        $result->setData(['balance' => $balance]);

        return $result;
    }
}
