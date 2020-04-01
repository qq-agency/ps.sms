<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

class Intis extends Base implements HasPreferences, HasWarning
{
    protected $provider = 'Intis';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_INTIS_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_INTIS_SHORT_NAME');
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_INTIS_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_INTIS_PASSWORD');
    }

    public function getWarning()
    {
        return Loc::getMessage('PS_SMS_INTIS_WARNING');
    }
}
