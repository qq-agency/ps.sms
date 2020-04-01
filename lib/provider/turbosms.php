<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasSender;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

class TurboSMS extends Base implements HasPreferences, HasSender, HasWarning
{
    protected $provider = 'TurboSMS';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_TURBOSMS_SHORT_NAME');
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
}
