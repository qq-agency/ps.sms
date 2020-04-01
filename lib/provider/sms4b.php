<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;

Loc::loadMessages(__FILE__);

class Sms4b extends Base implements HasPreferences
{
    protected $provider = 'Sms4b';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_SMS4B_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_SMS4B_SHORT_NAME');
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_SMS4B_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_SMS4B_PASSWORD');
    }
}
