<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Sender\Base;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Ps\Sms\Api\TurboSMS as Api;
use Ps\Sms\Interfaces\HasBalance;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasSender;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

class TurboSMS extends Base implements HasPreferences, HasWarning, HasBalance, HasSender
{
    private $login;

    private $password;

    private $sender;

    private $client;

    public function __construct()
    {
        try {
            $this->login = Option::get('ps.sms', 'turbosms_login');
            $this->password = Option::get('ps.sms', 'turbosms_password');
            $this->sender = Option::get('ps.sms', 'turbosms_sender');
        } catch (ArgumentNullException $e) {
        } catch (ArgumentOutOfRangeException $e) {
        }

        $this->client = new Api($this->login, $this->password);
    }

    public function sendMessage(array $messageFields)
    {
        if (!$this->canUse()) {
            $result = new SendMessage();
            $result->addError(new Error(Loc::getMessage('PS_SMS_TURBOSMS_CAN_USE_ERROR')));
            return $result;
        }

        $parameters = [
            'destination' => $messageFields['MESSAGE_TO'],
            'text' => $messageFields['MESSAGE_BODY'],
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
        return Loc::getMessage('PS_SMS_TURBOSMS_SHORT_NAME');
    }

    public function getId()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_ID');
    }

    public function getName()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_NAME');
    }

    public function getFromList()
    {
        return [
            [
                'id' => $this->sender,
                'name' => $this->sender
            ]
        ];
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_PASSWORD');
    }

    public function getSenderTitle()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_SENDER');
    }

    public function getWarning()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_WARNING');
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
