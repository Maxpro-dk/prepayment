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

class PrepaymentPartials extends ObjectModel
{
    public $id_cart;

    public $id_prepayment_last_activities;

    public $id_cart_rule;

    public $active;

    public $date_add;

    public $date_upd;


    public static $definition = array(
        'table' => 'prepayment_partials',
        'primary' => 'id_prepayment_partials',
        'fields' => array(
            'id_prepayment_last_activities' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_cart' =>                        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_cart_rule' =>                    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'active' =>                            array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>                        array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>                        array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function getPartialInstance($id_cart)
    {
        $id_partial = Db::getInstance()->getValue('
		SELECT `id_prepayment_partials`
		FROM `'._DB_PREFIX_.'prepayment_partials`
		WHERE `id_cart` = '.(int)$id_cart);

        $partial = new PrepaymentPartials((int)$id_partial);
        if (Validate::isLoadedObject($partial)) {
            return $partial;
        }

        return false;
    }

    public static function getIdCartRuleByIdCart($id_cart)
    {
        return (int)Db::getInstance()->getValue('
		SELECT pp.`id_cart_rule`
		FROM `'._DB_PREFIX_.'prepayment_partials` pp
		WHERE pp.`id_cart` = '.(int)$id_cart);
    }

    public static function partialExists($id_cart)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT `id_prepayment_partials`
		FROM `'._DB_PREFIX_.'prepayment_partials`
		WHERE `id_cart` = '.(int)$id_cart);
    }

    public static function deleteDiscountOnInvoice($order_invoice, $value_tax_incl, $value_tax_excl)
    {
        if (!Validate::isLoadedObject($order_invoice)) {
            return false;
        }

        // Update OrderInvoice
        $order_invoice->total_discount_tax_incl -= $value_tax_incl;
        $order_invoice->total_discount_tax_excl -= $value_tax_excl;
        $order_invoice->total_paid_tax_incl += $value_tax_incl;
        $order_invoice->total_paid_tax_excl += $value_tax_excl;

        return $order_invoice->update();
    }
}
