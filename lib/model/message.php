<?php

namespace Ps\Sms\Model;

class Message
{
    protected $phone;

    protected $text;

    protected $sender;

    public function __construct($phone, $text)
    {
        $this->phone = $phone;
        $this->text = $text;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function setSender($sender)
    {
        $this->sender = $sender;
    }
}
