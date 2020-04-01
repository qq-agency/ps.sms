<?php

namespace Ps\Sms\Provider;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\MessageService\Sender\Result\SendMessage;
use CUtil;
use Exception;
use Ps\Sms\ApiFactory;
use Ps\Sms\Model\Balance;
use Ps\Sms\Model\Message;
use Ps\Sms\Model\Sender;
use Ps\Sms\Model\SenderCollection;

abstract class Base extends \Bitrix\MessageService\Sender\Base
{
    /** @var $client \Ps\Sms\Api\Base */
    protected $client;

    protected $login;

    protected $sender = '';

    protected $password;

    protected $provider = '';

    public function __construct()
    {
        try {
            $this->login = Option::get('ps.sms', $this->getId().'_login');
            $this->password = Option::get('ps.sms', $this->getId().'_password');
            $this->sender = Option::get('ps.sms', $this->getId().'_sender');
        } catch (ArgumentNullException $e) {
        } catch (ArgumentOutOfRangeException $e) {
        }

        $this->client = ApiFactory::init($this->provider);
        $this->client->setCredentials($this->login, $this->password);
    }

    public function getId()
    {
        return CUtil::translit(mb_strtolower($this->provider), 'ru');
    }

    public function canUse()
    {
        return $this->login && $this->password;
    }

    /**
     * @param  array  $messageFields
     * @return SendMessage
     */
    public function sendMessage(array $messageFields)
    {
        $result = new SendMessage();

        try {
            $message = new Message($messageFields['MESSAGE_TO'], $messageFields['MESSAGE_BODY']);
            if ($messageFields['MESSAGE_FROM']) {
                $message->setSender($messageFields['MESSAGE_FROM']);
            }

            $this->client->send($message);
        } catch (Exception $e) {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /** @return int */
    public function getBalance()
    {
        try {
            $balance = $this->client->getBalance();
            if ($balance instanceof Balance) {
                return $balance->getAmount();
            }
        } catch (Exception $e) {
        }

        return 0;
    }

    /** @return array */
    public function getFromList()
    {
        $list = new SenderCollection();
        try {
            $list = $this->client->getSenderList();
        } catch (Exception $e) {
        }

        if ($sender = $this->sender) {
            $list->append(new Sender($sender));
        }

        return $list->toArray();
    }
}
