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

class PrepaymentLastActivities extends ObjectModel
{
    public $id_operation;

    public $id_order;

    public $id_wallet;

    public $id_customer;

    public $id_currency;

    public $reference;

    public $price;

    public $credits;

    public $extra_credits;

    public $paid;

    public $date_add;

    public $date_upd;


    public static $definition = array(
        'table' => 'prepayment_last_activities',
        'primary' => 'id_prepayment_last_activities',
        'fields' => array(
            'id_order' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'id_operation' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'id_wallet' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'id_customer' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'reference' =>        array('type' => self::TYPE_STRING),
            'price' =>            array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'credits' =>        array('type' => self::TYPE_FLOAT),
            'extra_credits' =>  array('type' => self::TYPE_FLOAT),
            'id_currency' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'paid' =>            array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>        array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' =>        array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    const DEPOSIT = 1;
    const ORDER = 2;
    const REFUND = 3;
    const DISBURSEMENT = 4;
    const GIFT = 5;
    const CUSTOM_DEPOSIT = 6;
    const CUSTOM_DISBURSEMENT = 7;

    public function add($autodate = true, $null_values = false)
    {
        $wallet = new PrepaymentWallets((int)$this->id_wallet);
        if (!Validate::isLoadedObject($wallet)) {
            return false;
        }

        if (!parent::add($autodate, $null_values)) {
            return false;
        }

        $old_wallet_balance = $wallet->total_amount;
        self::updateWallet($wallet);
        $new_wallet_balance = $wallet->total_amount;

        $prepayment = new Prepayment();
        $prepayment->processNotification(
            $this->id_customer,
            $this->id_operation,
            $old_wallet_balance,
            $new_wallet_balance,
            $this->id_currency
        );

        return true;
    }

    public function update($null_values = false)
    {
        $wallet = new PrepaymentWallets((int)$this->id_wallet);
        if (!Validate::isLoadedObject($wallet)) {
            return false;
        }

        if (!parent::update($null_values)) {
            return false;
        }

        $old_wallet_balance = $wallet->total_amount;
        self::updateWallet($wallet);
        $new_wallet_balance = $wallet->total_amount;

        $prepayment = new Prepayment();
        $prepayment->processNotification(
            $this->id_customer,
            $this->id_operation,
            $old_wallet_balance,
            $new_wallet_balance,
            $this->id_currency
        );

        return true;
    }

    public function delete()
    {
        $wallet = new PrepaymentWallets((int)$this->id_wallet);
        if (!Validate::isLoadedObject($wallet)) {
            return false;
        }

        if (!parent::delete()) {
            return false;
        }

        if ($this->id_operation == self::CUSTOM_DEPOSIT || $this->id_operation == self::CUSTOM_DISBURSEMENT) {
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_last_activities_lang` WHERE `id_prepayment_last_activities` = '.(int)$this->id);
        }

        $old_wallet_balance = $wallet->total_amount;
        self::updateWallet($wallet);
        $new_wallet_balance = $wallet->total_amount;

        $prepayment = new Prepayment();
        $prepayment->processNotification(
            $this->id_customer,
            $this->id_operation,
            $old_wallet_balance,
            $new_wallet_balance,
            $this->id_currency
        );

        return true;
    }

    public static function updateWallet(&$wallet)
    {
        if (!is_object($wallet) || !Validate::isLoadedObject($wallet)) {
            return false;
        }

        $wallet->total_deposits_amount = self::getTotalCredits(PrepaymentLastActivities::DEPOSIT, $wallet->id_customer);
        $wallet->total_deposits_amount += self::getTotalCredits(PrepaymentLastActivities::CUSTOM_DEPOSIT, $wallet->id_customer);
        $wallet->total_orders_amount = self::getTotalCredits(PrepaymentLastActivities::ORDER, $wallet->id_customer);
        $wallet->total_refunds_amount = self::getTotalCredits(PrepaymentLastActivities::REFUND, $wallet->id_customer);
        $wallet->total_disbursements_amount = self::getTotalCredits(PrepaymentLastActivities::DISBURSEMENT, $wallet->id_customer);
        $wallet->total_disbursements_amount += self::getTotalCredits(PrepaymentLastActivities::CUSTOM_DISBURSEMENT, $wallet->id_customer);
        $wallet->total_gifts_amount = self::getTotalCredits(PrepaymentLastActivities::GIFT, $wallet->id_customer);
        $wallet->total_amount = $wallet->total_deposits_amount + $wallet->total_gifts_amount + $wallet->total_refunds_amount - $wallet->total_orders_amount - $wallet->total_disbursements_amount;
        $wallet->update();
        return true;
    }

    public static function getLastActivities($id_customer = null, $limit = null)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT pla.*, plal.`label`
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		LEFT JOIN `'._DB_PREFIX_.'prepayment_last_activities_lang` plal ON pla.`id_prepayment_last_activities` = plal.`id_prepayment_last_activities`'
        .(isset($id_customer) ? ' WHERE pla.`id_customer` = '.(int)$id_customer : '').'
		ORDER BY pla.`date_add` DESC'
        .(isset($limit) ? ' LIMIT '.(int)$limit : ''));

        if (!$res) {
            return array();
        }

        return $res;
    }

    public static function getIdsByIdOrder($id_order)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT id_prepayment_last_activities
		FROM `'._DB_PREFIX_.'prepayment_last_activities`
		WHERE `id_order` = '.(int)$id_order.'');

        if (!$res) {
            return array();
        }

        return $res;
    }

    public static function getOperationsByIdOrder($id_order)
    {
        $ids = array();
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT id_operation
		FROM `'._DB_PREFIX_.'prepayment_last_activities`
		WHERE `id_order` = '.(int)$id_order.'');

        if (is_array($res) && count($res)) {
            foreach ($res as $id_operation) {
                $ids[] = $id_operation['id_operation'];
            }
        }

        return $ids;
    }

    public static function refundExists($id_order_slip)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT pr.`id_prepayment_refunds`
		FROM `'._DB_PREFIX_.'prepayment_refunds` pr
		LEFT JOIN `'._DB_PREFIX_.'prepayment_last_activities` pla ON pr.`id_prepayment_last_activities` = pla.`id_prepayment_last_activities`
		WHERE pr.`id_order_slip` = '.(int)$id_order_slip.'');
    }

    public static function getPendingMovements($id_operation)
    {
        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(*) AS nb_pending
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`id_operation` = '.(int)$id_operation.'
	 	AND pla.`paid` = 0');
    }

    public static function getTotalCredits($id_operation, $id_customer = null, $paid = true)
    {
        $credits = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT SUM(pla.`credits` + pla.`extra_credits`) as nb_credits, pla.`id_currency`
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`id_operation` = '.(int)$id_operation.
        (isset($id_customer) ? ' AND pla.`id_customer` = '.(int)$id_customer : '')
        .($paid ? ' AND pla.`paid` = "1"' : '').
        'GROUP BY pla.`id_currency`');

        $total_credits = 0;
        foreach ($credits as $credit) {
            $total_credits += Tools::convertPriceFull($credit['nb_credits'], Currency::getCurrencyInstance((int)$credit['id_currency']), Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS')));
        }

        return $total_credits;
    }

    public function getLabel()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT plal.`label`
		FROM `'._DB_PREFIX_.'prepayment_last_activities_lang` plal
		WHERE plal.`id_prepayment_last_activities` = '.(int)$this->id);
    }

    public static function getSummary($order, $amount = null, $wallet_payment = false)
    {
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $summary = array();
        $summary['price'] = 0;
        $summary['credits'] = 0;
        $summary['extra_credits'] = 0;
        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Currency::getCurrencyInstance((int)$order->id_currency);

        if ($wallet_payment) {
            $summary['price'] = (float)$order->total_paid;
            $summary['credits'] = (float)$amount;
        } else {
            $products = $order->getProductsDetail();
            foreach ($products as $product) {
                if (!PrepaymentPacks::packExists((int)$product['id_product'])) {
                    continue;
                }

                $summary['price'] += $product['total_price_tax_incl'];
                $summary['credits'] += Tools::convertPriceFull(PrepaymentPacks::getCredits($product['id_product']) * $product['product_quantity'], $from_currency, $to_currency);
                $summary['extra_credits'] += Tools::convertPriceFull(PrepaymentPacks::getExtraCredits($product['id_product']) * $product['product_quantity'], $from_currency, $to_currency);
            }
        }

        return $summary;
    }

    public static function getMovements()
    {
        $now = date('Y-m-d H:i:s');
        $time = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime($now)));

        return (int)Db::getInstance()->getValue('
		SELECT COUNT(pla.`id_prepayment_last_activities`)
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`date_upd` > "'.$time.'"');
    }

    public static function getVariation()
    {
        $now = date('Y-m-d H:i:s');
        $time = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime($now)));

        $deposits = 0;
        $orders = 0;
        $refunds = 0;
        $disbursements = 0;
        $gifts = 0;

        $movements = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT pla.`id_operation`, SUM(pla.`credits` + pla.`extra_credits`) as nb_credits
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`date_upd` < "'.$time.'"
		AND pla.`paid` = 1
		GROUP BY pla.`id_operation`');

        if (!$movements) {
            return 0;
        }

        foreach ($movements as $movement) {
            if ($movement['id_operation'] == PrepaymentLastActivities::DEPOSIT || $movement['id_operation'] == PrepaymentLastActivities::CUSTOM_DEPOSIT) {
                $deposits += $movement['nb_credits'];
            } elseif ($movement['id_operation'] == PrepaymentLastActivities::ORDER) {
                $orders += $movement['nb_credits'];
            } elseif ($movement['id_operation'] == PrepaymentLastActivities::REFUND) {
                $refunds += $movement['nb_credits'];
            } elseif ($movement['id_operation'] == PrepaymentLastActivities::DISBURSEMENT || $movement['id_operation'] == PrepaymentLastActivities::CUSTOM_DISBURSEMENT) {
                $disbursements += $movement['nb_credits'];
            } elseif ($movement['id_operation'] == PrepaymentLastActivities::GIFT) {
                $gifts += $movement['nb_credits'];
            }
        }

        $tendance = $deposits + $gifts + $refunds - $orders - $disbursements;
        return Tools::ps_round($tendance);
    }

    public static function getAmountEarned()
    {
        $default_currency = Currency::getCurrencyInstance((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        $amounts = 0;

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT pla.`id_currency`, SUM(pla.`price`) as price
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`paid`= 1
		AND pla.`id_operation` = '.PrepaymentLastActivities::DEPOSIT.'
		GROUP BY pla.`id_currency`');

        if (!$rows) {
            return $amounts;
        }

        foreach ($rows as $row) {
            $amounts += Tools::convertPriceFull($row['price'], Currency::getCurrencyInstance((int)$row['id_currency']), $default_currency);
        }

        return $amounts;
    }

    public static function getStats()
    {
        $result = array();

        $nb_activities = count(self::getLastActivities());

        $deposits = Db::getInstance()->getRow('
		SELECT SUM(pla.`credits` + pla.`extra_credits`) AS nb_credit, COUNT(pla.`id_prepayment_last_activities`) AS nb_operation
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`id_operation` = '.PrepaymentLastActivities::DEPOSIT.'
		HAVING COUNT(pla.`id_prepayment_last_activities`) > 0');
        if (!$deposits) {
            $credits_per_deposit = 0;
        } else {
            $credits_per_deposit = $deposits['nb_credit'] / $deposits['nb_operation'];
        }

        $orders = Db::getInstance()->getRow('
		SELECT SUM(pla.`credits` + pla.`extra_credits`) AS nb_credit, COUNT(pla.`id_prepayment_last_activities`) AS nb_operation
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`id_operation` = '.PrepaymentLastActivities::ORDER.'
		HAVING COUNT(pla.`id_prepayment_last_activities`) > 0');
        if (!$orders) {
            $credits_per_order = 0;
        } else {
            $credits_per_order = $orders['nb_credit'] / $orders['nb_operation'];
        }

        $deposit_per_order = 0;
        if (isset($deposits['nb_operation']) && isset($orders['nb_operation'])) {
            $deposit_per_order = $orders['nb_operation'] / $deposits['nb_operation'];
        }

        $currency = Currency::getCurrencyInstance(Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));

        $result['nb_activities'] = $nb_activities;
        $result['credits_per_deposit'] = Tools::displayPrice($credits_per_deposit, $currency);
        $result['credits_per_order'] = Tools::displayPrice($credits_per_order, $currency);
        $result['deposit_per_order'] = Tools::ps_round($deposit_per_order, 2);

        return $result;
    }

    public static function isPartial($id_last_activity, $active = true)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT `id_prepayment_partials`
		FROM `'._DB_PREFIX_.'prepayment_partials`
		WHERE `id_prepayment_last_activities` = '.(int)$id_last_activity.'
		AND `active` = '.(int)$active);
    }

    public function getPartialId()
    {
        return (int)Db::getInstance()->getValue('
		SELECT `id_prepayment_partials`
		FROM `'._DB_PREFIX_.'prepayment_partials`
		WHERE `id_prepayment_last_activities` = '.(int)$this->id);
    }
}
