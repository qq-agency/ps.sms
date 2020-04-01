<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

class SmsAero extends Base implements HasPreferences, HasWarning
{
    protected $provider = 'SmsAero';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_SMSAERO_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_SMSAERO_SHORT_NAME');
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_SMSAERO_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_SMSAERO_PASSWORD');
    }

    public function getWarning()
    {
        return Loc::getMessage('PS_SMS_SMSAERO_WARNING');
    }
}
