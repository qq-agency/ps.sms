<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;

Loc::loadMessages(__FILE__);

class IqSms extends Base implements HasPreferences
{
    protected $provider = 'IqSms';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_IQSMS_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_IQSMS_SHORT_NAME');
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_IQSMS_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_IQSMS_PASSWORD');
    }
}
