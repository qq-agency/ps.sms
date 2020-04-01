<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$moduleId = 'ps.sms';

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Sender\Base;
use Ps\Sms\Events;
use Ps\Sms\Interfaces\HasPreferences;
use Ps\Sms\Interfaces\HasSender;
use Ps\Sms\Interfaces\HasWarning;

Loc::loadMessages(__FILE__);

try {
    Loader::includeModule('messageservice');
    Loader::includeSharewareModule($moduleId);
} catch (LoaderException $e) {
}

$context = Context::getCurrent();
$post = $context->getRequest()->getPostList()->toArray();

if (is_array($post['settings']) && (count($post['settings']) > 0)) {
    foreach ($post['settings'] as $name => $val) {
        if (isset($val)) {
            Option::set($moduleId, $name, $val);
        } else {
            Option::delete($moduleId, ['name' => $name]);
        }
    }
}

$providers = [];
$services = new Events();
foreach ($services->registerProvider() as $provider) {
    if ($provider instanceof HasPreferences) {
        $providers[] = $provider;
    }
}

$tabs = [];
/** @var $provider Base */
foreach ($providers as $provider) {
    $tabs[] = [
        'DIV' => $provider->getId(),
        'TAB' => $provider->getName(),
        'TITLE' => Loc::getMessage(
            'PS_SMS_OPTIONS_TAB_TITLE',
            [
                '#NAME#' => $provider->getName(),
                '#SHORT_NAME#' => $provider->getShortName(),
            ]
        )
    ];
}

$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->Begin();

echo '<form name="'.$moduleId.'" method="POST" action="'.$APPLICATION->GetCurPage(
    ).'?mid='.$moduleId.'&lang='.LANGUAGE_ID.'" enctype="multipart/form-data">'.bitrix_sessid_post();

/** @var $provider Base */
foreach ($providers as $provider) {
    $tabControl->BeginNextTab();

    if ($provider->canUse()) {
        $balance = $provider->getBalance();
        ?>
        <tr>
            <td colspan="2">
                <?php

                $message = new CAdminMessage(
                    Loc::getMessage(
                        'PS_SMS_OPTIONS_BALANCE',
                        [
                            '#SUM#' => $balance
                        ]
                    )
                );
                $message->ShowNote(
                    Loc::getMessage(
                        'PS_SMS_OPTIONS_BALANCE',
                        [
                            '#SUM#' => $balance
                        ]
                    )
                );
                ?>
            </td>
        </tr>
        <?php
    }

    $loginField = $provider->getId().'_login';
    $passwordField = $provider->getId().'_password';
    $senderField = $provider->getId().'_sender';
    ?>

    <tr class="heading">
        <td colspan="2"><?= Loc::getMessage('PS_SMS_OPTIONS_CONNECTION') ?></td>
    </tr>
    <tr>
        <td width="40%" nowrap="" class="adm-detail-content-cell-l">
            <label for="ps_sms_<?= $loginField ?>"><?= $provider->getLoginTitle() ?></label>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
            <input type="text" id="ps_sms_<?= $loginField ?>"
                   name="settings[<?= $loginField ?>]"
                   value="<?= Option::get($moduleId, $loginField) ?>"/>
        </td>
    </tr>

    <tr>
        <td width="40%" nowrap="" class="adm-detail-content-cell-l">
            <label for="ps_sms_<?= $passwordField ?>"><?= $provider->getPasswordTitle() ?></label>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
            <input type="password" id="ps_sms_<?= $passwordField ?>"
                   name="settings[<?= $passwordField ?>]"
                   value="<?= Option::get($moduleId, $passwordField) ?>"/>
        </td>
    </tr>

    <?php

    if ($provider instanceof HasSender) { ?>
        <tr>
            <td width="40%" nowrap="" class="adm-detail-content-cell-l">
                <label for="ps_sms_<?= $senderField ?>"><?= $provider->getSenderTitle() ?></label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="text" id="ps_sms_<?= $senderField ?>"
                       name="settings[<?= $senderField ?>]"
                       value="<?= Option::get($moduleId, $senderField) ?>"/>
            </td>
        </tr>
        <?php
    }

    if ($provider instanceof HasWarning) { ?>
        <tr>
            <td colspan="2" align="center">
                <div class="adm-info-message-wrap" align="center">
                    <div class="adm-info-message">
                        <p><?= $provider->getWarning() ?></p>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }
}

$tabControl->End();

$tabControl->Buttons();

echo '<input type="hidden" name="update" value="Y" />';
echo '<input type="submit" name="save" value="'.Loc::getMessage('PS_SMS_OPTIONS_SAVE').'" class="adm-btn-save" />';
echo '<input type="reset" name="reset" value="'.Loc::getMessage('PS_SMS_OPTIONS_RESET').'" />';
echo '</form>';
$tabControl->End();
