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

    $sql = array();

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentWallets::$definition['table'].'`
				(
					`id_prepayment_wallets` int(10) unsigned NOT NULL auto_increment,
					`id_customer` int(10) unsigned NOT NULL,
					`total_amount` decimal(20,6) NOT NULL,
					`total_orders_amount` decimal(20,6) NOT NULL,
					`total_deposits_amount` decimal(20,6) NOT NULL,
					`total_gifts_amount` decimal(20,6) NOT NULL,
					`total_refunds_amount` decimal(20,6) NOT NULL,
					`total_disbursements_amount` decimal(20,6) NOT NULL,
					`active` tinyint(1) NOT NULL,
					`date_add` datetime NOT NULL,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY  (`id_prepayment_wallets`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentLastActivities::$definition['table'].'`
				(
					`id_prepayment_last_activities` int(10) unsigned NOT NULL auto_increment,
					`id_operation` int(10) unsigned NOT NULL,
					`id_order` int(10) unsigned NOT NULL,
					`id_customer` int(10) unsigned NOT NULL,
					`id_wallet` int(10) unsigned NOT NULL,
					`id_currency` int(10) unsigned NOT NULL,
					`reference` varchar(9),
					`price` decimal(20,6) NOT NULL,
					`credits` decimal(20,6) NOT NULL,
					`extra_credits` decimal(20,6) NOT NULL DEFAULT "0",
					`paid` tinyint(1) NOT NULL,
					`date_add` datetime NOT NULL,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY  (`id_prepayment_last_activities`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentLastActivities::$definition['table'].'_lang`
				(
					`id_prepayment_last_activities` int(10) unsigned NOT NULL auto_increment,
					`id_lang` int(10) unsigned NOT NULL,
					`label` varchar(64),
					PRIMARY KEY  (`id_prepayment_last_activities`, `id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentPacks::$definition['table'].'`
				(
					`id_prepayment_packs` int(10) unsigned NOT NULL auto_increment,
					`id_product` int(10) unsigned NOT NULL,
					`id_currency` int(10) unsigned NOT NULL,
					`credits` decimal(20,6) NOT NULL,
					`extra_credits` decimal(20,6) NOT NULL,
					`active` tinyint(1) NOT NULL,
					`date_add` datetime NOT NULL,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY  (`id_prepayment_packs`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentPacks::$definition['table'].'_lang`
				(
					`id_prepayment_packs` int(10) unsigned NOT NULL,
					`id_lang` int(10) unsigned NOT NULL,
					`name` varchar(32) NOT NULL,
					PRIMARY KEY  (`id_prepayment_packs`, `id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'prepayment_refunds`
				(
					`id_prepayment_refunds` int(10) unsigned NOT NULL auto_increment,
					`id_prepayment_last_activities` int(10) unsigned NOT NULL,
					`id_order_slip` int(10) unsigned NOT NULL,
					PRIMARY KEY  (`id_prepayment_refunds`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'`
				(
					`id_prepayment_gifts` int(10) unsigned NOT NULL auto_increment,
					`id_customer` int(10) unsigned NOT NULL DEFAULT "0",
					`date_from` datetime NOT NULL,
					`date_to` datetime NOT NULL,
					`quantity` int(10) unsigned NOT NULL DEFAULT "0",
					`quantity_per_user` int(10) unsigned NOT NULL DEFAULT "0",
					`priority` int(10) unsigned NOT NULL DEFAULT "1",
					`reference` varchar(9) DEFAULT NULL,
					`minimum_amount` decimal(17,2) NOT NULL DEFAULT "0",
					`minimum_amount_tax` tinyint(1) NOT NULL DEFAULT "0",
					`minimum_amount_currency` int(10) unsigned NOT NULL DEFAULT "0",
					`minimum_amount_shipping` tinyint(1) NOT NULL DEFAULT "0",
					`country_restriction` tinyint(1) NOT NULL DEFAULT "0",
					`carrier_restriction` tinyint(1) NOT NULL DEFAULT "0",
					`group_restriction` tinyint(1) NOT NULL DEFAULT "0",
					`product_restriction` tinyint(1) NOT NULL DEFAULT "0",
					`shop_restriction` tinyint(1) NOT NULL DEFAULT "0",
					`payment_restriction` tinyint(1) NOT NULL DEFAULT "0",
					`gift_percent` decimal(5,2) NOT NULL DEFAULT "0",
					`gift_amount` decimal(17,2) NOT NULL DEFAULT "0",
					`gift_tax` tinyint(1) NOT NULL DEFAULT "0",
					`gift_currency` int(10) unsigned NOT NULL DEFAULT "0",
					`highlight` tinyint(1) NOT NULL DEFAULT "0",
					`active` tinyint(1) NOT NULL DEFAULT "0",
					`date_add` datetime NOT NULL ,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY  (`id_prepayment_gifts`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_carrier`
				(
					`id_prepayment_gifts` int(10) unsigned NOT NULL,
					`id_carrier` int(10) unsigned NOT NULL,
					PRIMARY KEY  (`id_prepayment_gifts`, `id_carrier`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_country`
				(
					`id_prepayment_gifts` int(10) unsigned NOT NULL,
					`id_country` int(10) unsigned NOT NULL,
					PRIMARY KEY  (`id_prepayment_gifts`, `id_country`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_group`
				(
					`id_prepayment_gifts` int(10) unsigned NOT NULL,
					`id_group` int(10) unsigned NOT NULL,
					PRIMARY KEY  (`id_prepayment_gifts`, `id_group`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_shop`
				(
					`id_prepayment_gifts` int(10) unsigned NOT NULL,
					`id_shop` int(10) unsigned NOT NULL,
					PRIMARY KEY  (`id_prepayment_gifts`, `id_shop`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_payment`
				(
					`id_prepayment_gifts` int(10) unsigned NOT NULL,
					`id_module` int(10) unsigned NOT NULL,
					PRIMARY KEY  (`id_prepayment_gifts`, `id_module`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_product_rule`
				(
					`id_product_rule` int(10) unsigned NOT NULL auto_increment,
					`id_product_rule_group` int(10) unsigned NOT NULL,
					`type` enum("products","categories","attributes","manufacturers","suppliers") NOT NULL,
					PRIMARY KEY  (`id_product_rule`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_product_rule_group`
				(
					`id_product_rule_group` int(10) unsigned NOT NULL auto_increment,
					`id_prepayment_gifts` int(10) unsigned NOT NULL,
					`quantity` int(10) unsigned NOT NULL DEFAULT "1",
					PRIMARY KEY  (`id_product_rule_group`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';


    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_product_rule_value`
				(
					`id_product_rule` int(10) unsigned NOT NULL,
					`id_item` int(10) unsigned NOT NULL,
					PRIMARY KEY  (`id_product_rule`, `id_item`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentGifts::$definition['table'].'_lang`
				(
					`id_prepayment_gifts` int(10) unsigned NOT NULL,
					`id_lang` int(10) unsigned NOT NULL,
					`name` varchar(32) NOT NULL,
					`description` text DEFAULT NULL,
					PRIMARY KEY  (`id_prepayment_gifts`, `id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.PrepaymentPartials::$definition['table'].'`
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
