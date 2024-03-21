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

class PrepaymentWallets extends ObjectModel
{
    public $id_customer;

    public $total_amount = 0;

    public $total_orders_amount = 0;

    public $total_deposits_amount = 0;

    public $total_gifts_amount = 0;

    public $total_refunds_amount = 0;

    public $total_disbursements_amount = 0;

    public $active;

    public $date_add;

    public $date_upd;


    public static $definition = array(
        'table' => 'prepayment_wallets',
        'primary' => 'id_prepayment_wallets',
        'fields' => array(
            'id_customer' =>                array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'total_amount' =>                array('type' => self::TYPE_FLOAT),
            'total_orders_amount' =>        array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'total_deposits_amount' =>        array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'total_gifts_amount' =>        array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'total_refunds_amount' =>        array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'total_disbursements_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'active' =>                        array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>                    array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>                    array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function getWallets($limit = null)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT pw.*
		FROM `'._DB_PREFIX_.'prepayment_wallets` pw
		ORDER BY pw.`total_amount` DESC'
        .(isset($limit) ? ' LIMIT '.(int)$limit : ''));

        if (!$res) {
            return array();
        }

        return $res;
    }

    public static function getWalletInstance($id_customer)
    {
        $id_wallet = Db::getInstance()->getValue('
		SELECT `id_prepayment_wallets`
		FROM `'._DB_PREFIX_.'prepayment_wallets`
		WHERE `id_customer` = '.(int)$id_customer);

        $wallet = new PrepaymentWallets((int)$id_wallet);
        if (Validate::isLoadedObject($wallet)) {
            return $wallet;
        }

        return false;
    }

    public static function walletExists($id_customer)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT `id_prepayment_wallets`
		FROM `'._DB_PREFIX_.'prepayment_wallets`
		WHERE `id_customer` = '.(int)$id_customer);
    }

    public static function getBalance()
    {
        return (float)Db::getInstance()->getValue('
		SELECT SUM(pw.`total_amount`)
		FROM `'._DB_PREFIX_.'prepayment_wallets` pw');
    }

    public static function getStats()
    {
        $result = array();
        $nb_wallets = count(self::getWallets());
        $nb_customers = count(Customer::getCustomers());
        $wallet_per_customer = $nb_wallets / $nb_customers * 100;

        $active_wallets = Db::getInstance()->getValue('
		SELECT COUNT(`id_prepayment_wallets`)
		FROM `'._DB_PREFIX_.'prepayment_wallets`
		WHERE `total_amount` > 0');

        $unactive_wallets = Db::getInstance()->getValue('
		SELECT COUNT(`id_prepayment_wallets`)
		FROM `'._DB_PREFIX_.'prepayment_wallets`
		WHERE `total_amount` = 0');

        $result['nb_wallets'] = $nb_wallets;
        $result['wallet_per_customer'] = $wallet_per_customer;
        $result['active_wallets'] = $active_wallets;
        $result['unactive_wallets'] = $unactive_wallets;

        return $result;
    }
}
