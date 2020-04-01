<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\SenderCollection;

class Sms4b extends Base
{
    private $client;

    private $session;

    public function __construct()
    {
        parent::__construct();

        try {
            $this->client = new \SoapClient('https://sms4b.ru/ws/sms.asmx?wsdl', ['exceptions' => true]);
        } catch (\SoapFault $e) {
        }
    }

    public function __destruct()
    {
        $this->query(
            'CloseSession',
            [
                'SessionID' => $this->session
            ]
        );
    }

    private function query($method, $parameters = [])
    {
        return (array)$this->client->__soapCall($method, [$parameters]);
    }

    public function getAccount()
    {
        $this->getSession();

        $data = $this->query(
            'ParamSMS',
            [
                'SessionId' => $this->session,
            ]
        );

        return (array)$data['ParamSMSResult'];
    }

    private function getSession()
    {
        $data = $this->query(
            'StartSession',
            [
                'Login' => $this->login,
                'Password' => $this->password,
                'Gmt' => 3,
            ]
        );

        if ($data['StartSessionResult'] > 0) {
            $this->session = $data['StartSessionResult'];
        }

        return $data;
    }

    public function getBalance()
    {
        return new Balance();
    }

    public function getSenderList()
    {
        return new SenderCollection();
    }

    public function send($parameters)
    {
        $result = new Result();

        try {
            $data = $this->client->__soapCall('SendSMS', [$parameters]);

            $result->setData((array)$data);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));

            return $result;
        }

        return $result;
    }
}
