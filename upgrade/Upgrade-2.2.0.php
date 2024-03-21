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

function upgrade_module_2_2_0($object)
{
    // Create sql table
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentPartials::$definition['table'].'`
	(
		`id_prepayment_partials` int(10) unsigned NOT NULL auto_increment,
		`id_prepayment_last_activities` int(10) unsigned NOT NULL,
		`id_cart` int(10) unsigned NOT NULL,
		`id_cart_rule` int(10) unsigned NOT NULL,
		`active` tinyint(1) NOT NULL,
		`date_add` datetime NOT NULL,
		`date_upd` datetime NOT NULL,
		PRIMARY KEY  (`id_prepayment_partials`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    // Register hook
    if (!$object->registerHook('displayPaymentTop')
        || !$object->registerHook('actionCartSummary')
        || !$object->registerHook('cart')
        || !$object->registerHook('actionOrderStatusPostUpdate')) {
        return false;
    }

    // Install  new conf
    if (!Configuration::updateValue('WALLET_PARTIAL_PAYMENT', 0)) {
        return false;
    }

    return true;
}
