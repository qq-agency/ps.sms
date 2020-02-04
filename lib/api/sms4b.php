<?php

namespace Ps\Sms\Api;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Sms4b
{
    private $login;

    private $password;

    private $client;

    private $session;

    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;

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
        $result = new Result();

        try {
            $data = $this->client->__soapCall($method, [$parameters]);

            $result->setData((array)$data);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));

            return $result;
        }

        return $result;
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

    public function getAccount()
    {
        $result = new Result();

        $this->getSession();

        $response = $this->query(
            'ParamSMS',
            [
                'SessionId' => $this->session,
            ]
        );

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());

            return $result;
        }

        $data = $response->getData();
        $dataResult = (array)$data['ParamSMSResult'];

        $result->setData($dataResult);

        return $result;
    }

    private function getSession()
    {
        $result = new Result();

        $response = $this->query(
            'StartSession',
            [
                'Login' => $this->login,
                'Password' => $this->password,
                'Gmt' => 3,
            ]
        );

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
        }

        $data = $response->getData();

        if ($data['StartSessionResult'] > 0) {
            $this->session = $data['StartSessionResult'];
        }

        return $result;
    }
}
