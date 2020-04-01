<?php

namespace Ps\Sms\Api;

use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Message;
use Ps\Sms\Model\SenderCollection;

abstract class Base
{
    protected $login;

    protected $password;

    public function __construct()
    {
    }

    public function setCredentials($login, $password)
    {
        $this->login = $login;

        $this->password = $password;
    }

    /**
     * @param  Message  $parameters
     * @return true
     */
    abstract public function send($parameters);

    /** @return Balance */
    abstract public function getBalance();

    /** @return SenderCollection */
    abstract public function getSenderList();
}
