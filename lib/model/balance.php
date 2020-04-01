<?php

namespace Ps\Sms\Model;

class Balance
{
    protected $amount;

    public function __construct($amount = 0)
    {
        $this->amount = (double)str_replace(',', '.', $amount);
    }

    public function getAmount()
    {
        return $this->amount;
    }
}
