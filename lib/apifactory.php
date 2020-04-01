<?php

namespace Ps\Sms;

use Ps\Sms\Api\Base;
use RuntimeException;

class ApiFactory
{
    /**
     * @param  string  $name
     * @return Base
     */
    public static function init($name)
    {
        $className = '\\Ps\\Sms\\Api\\'.$name;
        $provider = new $className;

        if (!($provider instanceof Base)) {
            throw new RuntimeException('error');
        }

        return $provider;
    }
}
