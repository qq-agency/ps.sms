<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Ps\Sms\Events;

Loc::loadMessages(__FILE__);

class ps_sms extends CModule
{
    var $MODULE_ID = 'ps.sms';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__.'/version.php';
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('PS_SMS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('PS_SMS_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('PS_SMS_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('PS_SMS_PARTNER_URI');
    }

    public function DoInstall()
    {
        if (CheckVersion('18.0.0', ModuleManager::getVersion('messageservice'))) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('PS_SMS_VERSION_ERROR'));
            return false;
        }

        $this->InstallEvents();

        ModuleManager::registerModule($this->MODULE_ID);

        return true;
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'messageservice',
            'onGetSmsSenders',
            $this->MODULE_ID,
            Events::class,
            'registerProvider'
        );
    }

    public function DoUninstall()
    {
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'messageservice',
            'onGetSmsSenders',
            $this->MODULE_ID,
            Events::class,
            'registerProvider'
        );
    }
}
