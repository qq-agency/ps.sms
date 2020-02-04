<?php

namespace Ps\Sms;

use Ps\Sms\Provider\Intis;
use Ps\Sms\Provider\IqSms;
use Ps\Sms\Provider\MainSMS;
use Ps\Sms\Provider\RocketSMS;
use Ps\Sms\Provider\Sms4b;
use Ps\Sms\Provider\SmsAero;
use Ps\Sms\Provider\Smsc;
use Ps\Sms\Provider\TurboSMS;

class Events
{
    public function registerProvider()
    {
        $providers = [
            new Smsc(),
            new Intis(),
            new MainSMS(),
            new IqSms(),
            new SmsAero(),
            new RocketSMS()
        ];

        if (extension_loaded('soap')) {
            $providers = array_merge(
                $providers,
                [
                    new Sms4b(),
                    new TurboSMS()
                ]
            );
        }

        return $providers;
    }
}
