<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Sender\Base;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Ps\Sms\Api\MainSMS as Api;
use Ps\Sms\Interfaces\HasBalance;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

class MainSMS extends Base implements HasPreferences, HasWarning, HasBalance
{
    private $login;

    private $password;

    private $client;

    public function __construct()
    {
        try {
            $this->login = Option::get('ps.sms', $this->getId().'_login');
            $this->password = Option::get('ps.sms', $this->getId().'_password');
        } catch (ArgumentNullException $e) {
        } catch (ArgumentOutOfRangeException $e) {
        }

        $this->client = new Api($this->login, $this->password);
    }

    public function getId()
    {
        return Loc::getMessage('PS_SMS_MAINSMS_ID');
    }

    public function sendMessage(array $messageFields)
    {
        if (!$this->canUse()) {
            $result = new SendMessage();
            $result->addError(new Error(Loc::getMessage('PS_SMS_MAINSMS_CAN_USE_ERROR')));
            return $result;
        }

        $parameters = [
            'recipients' => $messageFields['MESSAGE_TO'],
            'message' => $messageFields['MESSAGE_BODY'],
        ];

        if ($messageFields['MESSAGE_FROM']) {
            $parameters['sender'] = $messageFields['MESSAGE_FROM'];
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
        return Loc::getMessage('PS_SMS_MAINSMS_SHORT_NAME');
    }

    public function getName()
    {
        return Loc::getMessage('PS_SMS_MAINSMS_NAME');
    }

    public function getFromList()
    {
        $data = $this->client->getSenderList();
        if ($data->isSuccess()) {
            return $data->getData();
        }

        return [];
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_MAINSMS_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_MAINSMS_PASSWORD');
    }

    public function getWarning()
    {
        return Loc::getMessage('PS_SMS_MAINSMS_WARNING');
    }

    public function getBalance()
    {
        $result = $this->client->getBalance();
        if (!$result->isSuccess()) {
            return 0;
        }

        $data = $result->getData();
        return str_replace(',', '.', $data['balance']);
    }
}
