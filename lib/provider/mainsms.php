<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

class MainSMS extends Base implements HasPreferences, HasWarning
{
    protected $provider = 'MainSMS';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_MAINSMS_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_MAINSMS_SHORT_NAME');
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
}
