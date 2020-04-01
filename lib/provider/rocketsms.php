<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasSender;

Loc::loadMessages(__FILE__);

class RocketSMS extends Base implements HasPreferences, HasSender
{
    protected $provider = 'RocketSMS';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_ROCKETSMS_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_ROCKETSMS_SHORT_NAME');
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_ROCKETSMS_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_ROCKETSMS_PASSWORD');
    }

    public function getSenderTitle()
    {
        return Loc::getMessage('PS_SMS_ROCKETSMS_SENDER');
    }
}
