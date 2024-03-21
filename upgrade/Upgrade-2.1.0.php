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

function upgrade_module_2_1_0($object)
{
    if (!$object->registerHook('actionPaymentCCAdd')) {
        return false;
    }

    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentLastActivities::$definition['table'].'_lang`
	(
		`id_prepayment_last_activities` int(10) unsigned NOT NULL auto_increment,
		`id_lang` int(10) unsigned NOT NULL,
		`label` varchar(64),
		PRIMARY KEY  (`id_prepayment_last_activities`, `id_lang`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    return true;
}
