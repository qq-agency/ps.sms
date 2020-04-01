<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\SenderCollection;

class TurboSMS extends Base
{
    private $client;

    public function __construct()
    {
        parent::__construct();

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
    }

    private function auth()
    {
        $response = $this->client->Auth(
            [
                'login' => $this->login,
                'password' => $this->password
            ]
        );

        if ($response->AuthResult !== Loc::getMessage('PS_SMS_TURBOSMS_AUTH_SUCCESS_MESSAGE')) {
            throw new \RuntimeException('Auth error');
        }

        return true;
    }

    public function getBalance()
    {
        $this->auth();

        return new Balance($this->client->GetCreditBalance()->GetCreditBalanceResult);
    }

    public function getSenderList()
    {
        return new SenderCollection();
    }

    // todo
    public function send($parameters)
    {
        $this->auth();

        $response = $this->client->SendSMS($parameters);

        $message = $response->SendSMSResult->ResultArray[0];
        if ($message !== Loc::getMessage('PS_SMS_TURBOSMS_SEND_SUCCESS_MESSAGE')) {
            throw new \RuntimeException($message);
        }

        return true;
    }
}
