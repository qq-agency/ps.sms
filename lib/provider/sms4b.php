<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Sender\Base;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Ps\Sms\Api\Sms4b as Api;
use Ps\Sms\Interfaces\HasBalance;
use Ps\Sms\Interfaces\HasPreferences;

Loc::loadMessages(__FILE__);

class Sms4b extends Base implements HasPreferences, HasBalance
{
    private $login;

    private $password;

    private $client;

    public function __construct()
    {
        try {
            $this->login = Option::get('ps.sms', 'sms4b_login');
            $this->password = Option::get('ps.sms', 'sms4b_password');
        } catch (ArgumentNullException $e) {
        } catch (ArgumentOutOfRangeException $e) {
        }

        $this->client = new Api($this->login, $this->password);
    }

    public function sendMessage(array $messageFields)
    {
        if (!$this->canUse()) {
            $result = new SendMessage();
            $result->addError(new Error(Loc::getMessage('PS_SMS_SMS4B_CAN_USE_ERROR')));
            return $result;
        }

        $parameters = [
            'Login' => $this->login,
            'Password' => $this->password,
            'Phone' => $messageFields['MESSAGE_TO'],
            'Text' => $messageFields['MESSAGE_BODY'],
        ];

        if ($messageFields['MESSAGE_FROM']) {
            $parameters['Source'] = $messageFields['MESSAGE_FROM'];
        }

        $result = new SendMessage();
        $response = $this->client->send($parameters);

        if (!$response->isSuccess()) {
            $result->addErrors($response->getErrors());
            return $result;
        }

        return $result;
    }

    public function canUse()
    {
        return $this->login && $this->password;
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_SMS4B_SHORT_NAME');
    }

    public function getId()
    {
        return Loc::getMessage('PS_SMS_SMS4B_ID');
    }

    public function getName()
    {
        return Loc::getMessage('PS_SMS_SMS4B_NAME');
    }

    public function getFromList()
    {
        $data = $this->client->getAccount();

        if ($data->isSuccess()) {
            $result = $data->getData();

            $addresses = explode(PHP_EOL, $result['Addresses']);

            $senders = [];
            foreach ($addresses as $sender) {
                $senders[] = [
                    'id' => $sender,
                    'name' => $sender
                ];
            }

            return $senders;
        }

        return [];
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_SMS4B_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_SMS4B_PASSWORD');
    }

    public function getBalance()
    {
        $result = $this->client->getAccount();
        if (!$result->isSuccess()) {
            return 0;
        }

        $data = $result->getData();

        return $data['Rest'];
    }
}
