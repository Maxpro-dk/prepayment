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
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_wallets`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_last_activities`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_last_activities_lang`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_packs`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_packs_lang`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_carrier`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_country`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_group`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_product_rule`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_product_rule_group`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_product_rule_value`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_shop`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_gifts_lang`';
    $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'prepayment_partials`';
