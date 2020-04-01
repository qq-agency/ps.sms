<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

class Smsc extends Base implements HasPreferences, HasWarning
{
    protected $provider = 'Smsc';

    public function getName()
    {
        return Loc::getMessage('PS_SMS_SMSC_NAME');
    }

    public function getShortName()
    {
        return Loc::getMessage('PS_SMS_SMSC_SHORT_NAME');
    }

    public function getLoginTitle()
    {
        return Loc::getMessage('PS_SMS_SMSC_LOGIN');
    }

    public function getPasswordTitle()
    {
        return Loc::getMessage('PS_SMS_SMSC_PASSWORD');
    }

    public function getWarning()
    {
        $context = Context::getCurrent();

        return Loc::getMessage(
            'PS_SMS_SMSC_WARNING',
            [
                '#IP#' => gethostbyname($context->getServer()->getServerName())
            ]
        );
    }
}
