<?php
/**
* 2017 - Keyrnel SARL
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    Keyrnel
* @copyright 2017 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_4_0($object)
{
    // Install  new conf
    if (!Configuration::updateValue('WALLET_NOTIFICATION_DEPOSIT', 0)
        || !Configuration::updateValue('WALLET_NOTIFICATION_ORDER', 0)
        || !Configuration::updateValue('WALLET_NOTIFICATION_DISBURSEMENT', 0)
        || !Configuration::updateValue('WALLET_NOTIFICATION_REFUND', 0)
        || !Configuration::updateValue('WALLET_NOTIFICATION_GIFT', 0)) {
        return false;
    }

    //init mails
    $object->initMails();

    return true;
}
