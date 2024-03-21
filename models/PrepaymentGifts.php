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

class PrepaymentGifts extends ObjectModel
{
    /* Filters used when retrieving the cart rules applied to a cart of when calculating the value of a reduction */
    const FILTER_ACTION_ALL = 1;
    const FILTER_ACTION_SHIPPING = 2;
    const FILTER_ACTION_REDUCTION = 3;
    const FILTER_ACTION_GIFT = 4;
    const FILTER_ACTION_ALL_NOCAP = 5;

    const BO_ORDER_CODE_PREFIX = 'BO_ORDER_';

    public $id;
    public $name;
    public $id_customer;
    public $date_from;
    public $date_to;
    public $description;
    public $quantity = 1;
    public $quantity_per_user = 1;
    public $priority = 1;
    public $partial_use = 1;
    public $reference;
    public $minimum_amount;
    public $minimum_amount_tax;
    public $minimum_amount_currency;
    public $minimum_amount_shipping;
    public $country_restriction;
    public $carrier_restriction;
    public $group_restriction;
    public $product_restriction;
    public $shop_restriction;
    public $payment_restriction;
    public $gift_percent;
    public $gift_amount;
    public $gift_tax;
    public $gift_currency;
    public $highlight = 1;
    public $active = 1;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'prepayment_gifts',
        'primary' => 'id_prepayment_gifts',
        'multilang' => true,
        'fields' => array(
            'id_customer' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_from' =>                array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'date_to' =>                array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'quantity' =>                array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'quantity_per_user' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'priority' =>                array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'reference' =>                array('type' => self::TYPE_STRING),
            'minimum_amount' =>        array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'minimum_amount_tax' =>    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'minimum_amount_currency' =>array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'minimum_amount_shipping' =>array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'country_restriction' =>    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'carrier_restriction' =>    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'group_restriction' =>        array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'product_restriction' =>    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'shop_restriction' =>        array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'payment_restriction' =>    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'gift_percent' =>            array('type' => self::TYPE_FLOAT, 'validate' => 'isPercentage'),
            'gift_amount' =>            array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'gift_tax' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'gift_currency' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'highlight' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>                array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>                array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

            'name' =>                    array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 254),
            'description' =>            array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 65534),
        ),
    );

    public function delete()
    {
        if (!parent::delete()) {
            return false;
        }

        $r = Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_carrier` WHERE `id_prepayment_gifts` = '.(int)$this->id);
        $r &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_shop` WHERE `id_prepayment_gifts` = '.(int)$this->id);
        $r &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_group` WHERE `id_prepayment_gifts` = '.(int)$this->id);
        $r &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_country` WHERE `id_prepayment_gifts` = '.(int)$this->id);
        $r &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_payment` WHERE `id_prepayment_gifts` = '.(int)$this->id);
        $r &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule_group` WHERE `id_prepayment_gifts` = '.(int)$this->id);
        $r &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule` WHERE `id_product_rule_group`
			NOT IN (SELECT `id_product_rule_group` FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule_group`)');
        $r &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule_value` WHERE `id_product_rule`
			NOT IN (SELECT `id_product_rule` FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule`)');

        return $r;
    }

    public static function getGifts($limit = null)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT pg.*, pgl.`name`
		FROM `'._DB_PREFIX_.'prepayment_gifts` pg
		LEFT JOIN `'._DB_PREFIX_.'prepayment_gifts_lang` pgl on pg.`id_prepayment_gifts` = pgl.`id_prepayment_gifts`
		WHERE pgl.`id_lang` = '.Context::getContext()->language->id.'
		ORDER BY pg.`priority` ASC, pg.`date_to`, pg.`id_prepayment_gifts`'
        .(isset($limit) ? ' LIMIT '.(int)$limit : ''));

        if (!$res) {
            return array();
        }

        return $res;
    }

    protected function getIdModule($module)
    {
        return $module['id_module'];
    }

    public function getAssociatedRestrictions($type, $active_only, $i18n)
    {
        $array = array('selected' => array(), 'unselected' => array());

        if (!in_array($type, array('country', 'carrier', 'group', 'shop', 'payment'))) {
            return false;
        }

        $shop_list = '';
        if ($type == 'shop') {
            $shops = Shop::getShops(true, null, true);
            if (count($shops)) {
                $shop_list = ' AND t.id_shop IN ('.implode(',', array_map('intval', $shops)).')';
            }
        }

        $payment_list = '';
        if ($type == 'payment') {
            $payments = PaymentModule::getInstalledPaymentModules();
            if (count($payments)) {
                $payment_list = ' AND t.id_module IN ('.implode(',', array_map(array($this, 'getIdModule'), $payments)).')';
            }
        }

        if (!Validate::isLoadedObject($this) || $this->{$type.'_restriction'} == 0) {
            $array['selected'] = Db::getInstance()->executeS('
			SELECT t.*'.($i18n ? ', tl.*' : '').', 1 as selected
			FROM `'._DB_PREFIX_.($type == 'payment' ? 'module' : $type).'` t
			'.($i18n ? 'LEFT JOIN `'._DB_PREFIX_.$type.'_lang` tl ON (t.id_'.$type.' = tl.id_'.$type.' AND tl.id_lang = '.(int)Context::getContext()->language->id.')' : '').'
			WHERE 1
			'.($active_only ? 'AND t.active = 1' : '').'
			'.(in_array($type, array('carrier', 'shop')) ? ' AND t.deleted = 0' : '').
            ($type == 'payment' ? $payment_list : $shop_list).
            ' ORDER BY name ASC');
        } else {
            $resource = Db::getInstance()->query(
                'SELECT t.*'.($i18n ? ', tl.*' : '').', IF(crt.id_'.($type == 'payment' ? 'module' : $type).' IS NULL, 0, 1) as selected
    			FROM `'._DB_PREFIX_.($type == 'payment' ? 'module' : $type).'` t
    			'.($i18n ? 'LEFT JOIN `'._DB_PREFIX_.$type.'_lang` tl ON (t.id_'.$type.' = tl.id_'.$type.' AND tl.id_lang = '.(int)Context::getContext()->language->id.')' : '').'
    			LEFT JOIN (SELECT id_'.($type == 'payment' ? 'module' : $type).' FROM `'._DB_PREFIX_.'prepayment_gifts_'.$type.'` WHERE id_prepayment_gifts = '.(int)$this->id.') crt
    				ON t.id_'.($type == 'carrier' ? 'reference' : ($type == 'payment' ? 'module' : $type)).' = crt.id_'.($type == 'payment' ? 'module' : $type).'
    			WHERE 1 '.($active_only ? ' AND t.active = 1' : '').
                ($type == 'payment' ? $payment_list : $shop_list).
                (in_array(
                    $type,
                    array('carrier', 'shop')
                ) ? ' AND t.deleted = 0' : '').
                ' ORDER BY name ASC',
                false
            );
            while ($row = Db::getInstance()->nextRow($resource)) {
                $array[($row['selected'] || $this->{$type.'_restriction'} == 0) ? 'selected' : 'unselected'][] = $row;
            }
        }

        return $array;
    }

    public static function getIdByReference($reference)
    {
        if (!Validate::isCleanHtml($reference)) {
            return false;
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_prepayment_gifts` FROM `'._DB_PREFIX_.'prepayment_gifts` WHERE `reference` = \''.pSQL($reference).'\'');
    }

    public static function getCustomerGifts($id_lang, $id_customer, $active = false, $include_generic = true, $in_stock = false, Cart $cart = null)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT *
		FROM `'._DB_PREFIX_.'prepayment_gifts` pg
		LEFT JOIN `'._DB_PREFIX_.'prepayment_gifts_lang` pgl ON (pg.`id_prepayment_gifts` = pgl.`id_prepayment_gifts` AND pgl.`id_lang` = '.(int)$id_lang.')
		WHERE (
			pg.`id_customer` = '.(int)$id_customer.' OR pg.group_restriction = 1
			'.($include_generic ? 'OR pg.`id_customer` = 0' : '').'
		)
		AND pg.date_from < "'.date('Y-m-d H:i:s').'"
		AND pg.date_to > "'.date('Y-m-d H:i:s').'"
		'.($active ? 'AND pg.`active` = 1' : '').'
		'.($in_stock ? 'AND pg.`quantity` > 0' : ''));

        // Remove cart rule that does not match the customer groups
        $customer_groups = Customer::getGroupsStatic($id_customer);
        foreach ($result as $key => $gift) {
            if ($gift['group_restriction']) {
                $gift_groups = Db::getInstance()->executeS('SELECT id_group FROM '._DB_PREFIX_.'prepayment_gifts_group WHERE id_prepayment_gifts = '.(int)$gift['id_prepayment_gifts']);
                foreach ($gift_groups as $gift_group) {
                    if (in_array($gift_group['id_group'], $customer_groups)) {
                        continue 2;
                    }
                }

                unset($result[$key]);
            }
        }

        foreach ($result as $key => $gift) {
            if ($gift['quantity_per_user']) {
                $quantity_used = self::usedByCustomer((int)$id_customer, $gift['reference']);
                $gift['quantity_per_user'] = $gift['quantity_per_user'] - $quantity_used;
            } else {
                $gift['quantity_per_user'] = 0;
            }

            if ($gift['quantity_per_user'] == 0) {
                unset($result[$key]);
            }
        }

        foreach ($result as $key => $gift) {
            if ($gift['shop_restriction']) {
                $gift_shops = Db::getInstance()->executeS('SELECT id_shop FROM '._DB_PREFIX_.'prepayment_gifts_shop WHERE id_prepayment_gifts = '.(int)$gift['id_prepayment_gifts']);
                foreach ($gift_shops as $gift_shop) {
                    if (Shop::isFeatureActive() && ($gift_shop['id_shop'] == Context::getContext()->shop->id)) {
                        continue 2;
                    }
                }
                unset($result[$key]);
            }
        }

        return $result;
    }

    public static function usedByCustomer($id_customer, $reference)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT pla.`reference`
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON pla.`id_order` = o.`id_order`
		WHERE pla.`reference` = "'.$reference.'"
		AND o.`id_customer` = '.(int)$id_customer);
    }

    public static function deleteByIdCustomer($id_customer)
    {
        $return = true;
        $gifts = new PrestaShopCollection('PrepaymentGifts');
        $gifts->where('id_customer', '=', $id_customer);
        foreach ($gifts as $gift) {
            $return &= $gift->delete();
        }
        return $return;
    }

    public function getProductRuleGroups()
    {
        if (!Validate::isLoadedObject($this) || $this->product_restriction == 0) {
            return array();
        }

        $product_rule_groups = array();
        $result = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'prepayment_gifts_product_rule_group WHERE id_prepayment_gifts = '.(int)$this->id);
        foreach ($result as $row) {
            if (!isset($product_rule_groups[$row['id_product_rule_group']])) {
                $product_rule_groups[$row['id_product_rule_group']] = array('id_product_rule_group' => $row['id_product_rule_group'], 'quantity' => $row['quantity']);
            }
            $product_rule_groups[$row['id_product_rule_group']]['product_rules'] = $this->getProductRules($row['id_product_rule_group']);
        }
        return $product_rule_groups;
    }

    public function getProductRules($id_product_rule_group)
    {
        if (!Validate::isLoadedObject($this) || $this->product_restriction == 0) {
            return array();
        }

        $product_rules = array();
        $results = Db::getInstance()->executeS('
		SELECT *
		FROM '._DB_PREFIX_.'prepayment_gifts_product_rule pr
		LEFT JOIN '._DB_PREFIX_.'prepayment_gifts_product_rule_value prv ON pr.id_product_rule = prv.id_product_rule
		WHERE pr.id_product_rule_group = '.(int)$id_product_rule_group);
        foreach ($results as $row) {
            if (!isset($product_rules[$row['id_product_rule']])) {
                $product_rules[$row['id_product_rule']] = array('type' => $row['type'], 'values' => array());
            }
            $product_rules[$row['id_product_rule']]['values'][] = $row['id_item'];
        }
        return $product_rules;
    }

    public function checkValidity($order, Context $context, $already_in_cart = false, $display_error = true)
    {
        if (!$this->active) {
            return (!$display_error) ? false : Tools::displayError('This gift is disabled');
        }
        if (!$this->quantity) {
            return (!$display_error) ? false : Tools::displayError('This gift has already been used');
        }
        if (strtotime($this->date_from) > time()) {
            return (!$display_error) ? false : Tools::displayError('This gift is not valid yet');
        }
        if (strtotime($this->date_to) < time()) {
            return (!$display_error) ? false : Tools::displayError('This gift has expired');
        }

        if ($context->cart->id_customer) {
            $quantity_used = Db::getInstance()->getValue('
			SELECT count(*)
			FROM '._DB_PREFIX_.'orders o
			LEFT JOIN '._DB_PREFIX_.'prepayment_last_activities pla ON o.id_order = pla.id_order
			WHERE o.id_customer = '.$context->cart->id_customer.'
			AND pla.reference = "'.$this->reference.'"
			AND '.(int)Configuration::get('PS_OS_ERROR').' != o.current_state
			');
            if ($quantity_used + 1 > $this->quantity_per_user) {
                return (!$display_error) ? false : Tools::displayError('You cannot use this gift anymore (usage limit reached)');
            }
        }

        // Get an intersection of the customer groups and the cart rule groups (if the customer is not logged in, the default group is 1)
        if ($this->group_restriction) {
            $id_gift = (int)Db::getInstance()->getValue('
			SELECT pgg.id_prepayment_gifts
			FROM '._DB_PREFIX_.'prepayment_gifts_group pgg
			WHERE pgg.id_prepayment_gifts = '.(int)$this->id.'
			AND pgg.id_group '.($context->cart->id_customer ? 'IN (SELECT cg.id_group FROM '._DB_PREFIX_.'customer_group cg WHERE cg.id_customer = '.(int)$context->cart->id_customer.')' : '= 1'));
            if (!$id_gift) {
                return (!$display_error) ? false : Tools::displayError('You cannot use this gift');
            }
        }

        // Check if the customer delivery address is usable with the cart rule
        if ($this->country_restriction) {
            if (!$context->cart->id_address_delivery) {
                return (!$display_error) ? false : Tools::displayError('You must choose a delivery address before applying this voucher to your order');
            }
            $id_gift = (int)Db::getInstance()->getValue('
			SELECT pgc.id_prepayment_gifts
			FROM '._DB_PREFIX_.'prepayment_gifts_country pgc
			WHERE pgc.prepayment_gifts = '.(int)$this->id.'
			AND pgc.id_country = (SELECT a.id_country FROM '._DB_PREFIX_.'address a WHERE a.id_address = '.(int)$context->cart->id_address_delivery.' LIMIT 1)');
            if (!$id_gift) {
                return (!$display_error) ? false : Tools::displayError('You cannot use this gift in your country of delivery');
            }
        }

        // Check if the carrier chosen by the customer is usable with the cart rule
        if ($this->carrier_restriction) {
            if (!$context->cart->id_carrier) {
                return (!$display_error) ? false : Tools::displayError('You must choose a carrier before applying this gift to your order');
            }
            $id_gift = (int)Db::getInstance()->getValue('
			SELECT pcg.id_prepayment_gifts
			FROM '._DB_PREFIX_.'prepayment_gifts_carrier pgc
			INNER JOIN '._DB_PREFIX_.'carrier c ON (c.id_reference = pgc.id_carrier AND c.deleted = 0)
			WHERE pgc.id_prepayment_gifts = '.(int)$this->id.'
			AND c.id_carrier = '.(int)$context->cart->id_carrier);
            if (!$id_gift) {
                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with this carrier');
            }
        }

        // Check if the cart rules appliy to the shop browsed by the customer
        if ($this->shop_restriction && $context->shop->id && Shop::isFeatureActive()) {
            $id_gift = (int)Db::getInstance()->getValue('
			SELECT pgs.id_prepayment_gifts
			FROM '._DB_PREFIX_.'prepayment_gifts_shop pgs
			WHERE pgs.id_prepayment_gifts = '.(int)$this->id.'
			AND pgs.id_shop = '.(int)$context->shop->id);
            if (!$id_gift) {
                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher');
            }
        }

        // Check if the payment chosen by the customer is usuable with the gift rule
        if ($this->payment_restriction && $order) {
            $id_gift = (int)Db::getInstance()->getValue('
			SELECT pgp.id_prepayment_gifts
			FROM '._DB_PREFIX_.'prepayment_gifts_payment pgp
			WHERE pgp.id_prepayment_gifts = '.(int)$this->id.'
			AND pgp.id_module = '.(int)Module::getModuleIdByName($order->module));
            if (!$id_gift) {
                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with this payment method');
            }
        }

        // Check if the products chosen by the customer are usable with the cart rule
        if ($this->product_restriction) {
            $r = $this->checkProductRestrictions($context, false, $display_error, $already_in_cart);
            if ($r !== false && $display_error) {
                return $r;
            } elseif (!$r && !$display_error) {
                return false;
            }
        }

        // Check if the cart rule is only usable by a specific customer, and if the current customer is the right one
        if ($this->id_customer && $context->cart->id_customer != $this->id_customer) {
            if (!Context::getContext()->customer->isLogged()) {
                return (!$display_error) ? false : (Tools::displayError('You cannot use this voucher').' - '.Tools::displayError('Please log in'));
            }
            return (!$display_error) ? false : Tools::displayError('You cannot use this voucher');
        }

        if ($this->minimum_amount) {
            // Minimum amount is converted to the contextual currency
            $minimum_amount = $this->minimum_amount;
            if ($this->minimum_amount_currency != Context::getContext()->currency->id) {
                $minimum_amount = Tools::convertPriceFull($minimum_amount, Currency::getCurrencyInstance($this->minimum_amount_currency), Context::getContext()->currency);
            }

            $cart_total = $context->cart->getOrderTotal($this->minimum_amount_tax, Cart::ONLY_PRODUCTS);
            if ($this->minimum_amount_shipping) {
                $cart_total += $context->cart->getOrderTotal($this->minimum_amount_tax, Cart::ONLY_SHIPPING);
            }

            if ($cart_total < $minimum_amount) {
                return (!$display_error) ? false : Tools::displayError('You have not reached the minimum amount required to use this voucher');
            }
        }

        $nb_products = Cart::getNbProducts($context->cart->id);
        if (!$nb_products) {
            return (!$display_error) ? false : Tools::displayError('Cart is empty');
        }

        if (!$display_error) {
            return true;
        }
    }

    public function getContextualValue($use_tax, Context $context = null, $filter = null, $package = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        if (!$filter) {
            $filter = PrepaymentGifts::FILTER_ACTION_ALL;
        }

        $all_products = $context->cart->getProducts();
        $package_products = (is_null($package) ? $all_products : $package['products']);

        $gift_value = 0;

        $cache_id = 'getContextualValue_'.(int)$this->id.'_'.(int)$use_tax.'_'.(int)$context->cart->id.'_'.(int)$filter;
        foreach ($package_products as $product) {
            $cache_id .= '_'.(int)$product['id_product'].'_'.(int)$product['id_product_attribute'];
        }

        if (Cache::isStored($cache_id)) {
            return Cache::retrieve($cache_id);
        }

        if (in_array($filter, array(PrepaymentGifts::FILTER_ACTION_ALL, PrepaymentGifts::FILTER_ACTION_ALL_NOCAP, PrepaymentGifts::FILTER_ACTION_REDUCTION))) {
            // Discount (%) on the whole order
            if ($this->gift_percent) {
                // Do not give a reduction on free products!
                $order_total = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS, $package_products);
                $gift_value += $order_total * $this->gift_percent / 100;
            }

            // Discount (¤)
            if ($this->gift_amount) {
                $prorata = 1;
                if (!is_null($package) && count($all_products)) {
                    $total_products = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS);
                    if ($total_products) {
                        $prorata = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS, $package['products']) / $total_products;
                    }
                }

                $gift_amount = $this->gift_amount;

                // If we need to convert the voucher value to the cart currency
                if ($this->gift_currency != $context->currency->id) {
                    $voucher_currency = Currency::getCurrencyInstance($this->gift_currency);
                    // First we convert the voucher value to the default currency
                    if ($gift_amount == 0 || $voucher_currency->conversion_rate == 0) {
                        $gift_amount = 0;
                    } else {
                        $gift_amount /= $voucher_currency->conversion_rate;
                    }

                    // Then we convert the voucher value in the default currency into the cart currency
                    $gift_amount *= $context->currency->conversion_rate;
                    $gift_amount = Tools::ps_round($gift_amount, 2);
                }

                // If it has the same tax application that you need, then it's the right value, whatever the product!
                if ($this->gift_tax == $use_tax) {
                    // The reduction cannot exceed the products total, except when we do not want it to be limited (for the partial use calculation)
                    if ($filter != PrepaymentGifts::FILTER_ACTION_ALL_NOCAP) {
                        $cart_amount = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS);
                        $gift_amount = min($gift_amount, $cart_amount);
                    }
                    $gift_value += $prorata * $gift_amount;
                } else {
                    // Discount (¤) on the whole order
                    $cart_amount_ti = $context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
                    $cart_amount_te = $context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);

                    // The reduction cannot exceed the products total, except when we do not want it to be limited (for the partial use calculation)
                    if ($filter != CartRule::FILTER_ACTION_ALL_NOCAP) {
                        $gift_amount = min($gift_amount, $this->gift_tax ? $cart_amount_ti : $cart_amount_te);
                    }

                    $cart_vat_amount = $cart_amount_ti - $cart_amount_te;

                    if ($cart_vat_amount == 0 || $cart_amount_te == 0) {
                        $cart_average_vat_rate = 0;
                    } else {
                        $cart_average_vat_rate = Tools::ps_round($cart_vat_amount / $cart_amount_te, 3);
                    }

                    if ($this->gift_tax && !$use_tax) {
                        $gift_value += $prorata * $gift_amount / (1 + $cart_average_vat_rate);
                    } elseif (!$this->gift_tax && $use_tax) {
                        $gift_value += $prorata * $gift_amount * (1 + $cart_average_vat_rate);
                    }
                }
            }
        }

        Cache::store($cache_id, $gift_value);
        return $gift_value;
    }

    public static function getGift($order = null, Context $context = null)
    {
        if ($context === null) {
            $context = Context::getContext();
        }
        if (!Validate::isLoadedObject($context->cart)) {
            return;
        }
        if (!($wallet = PrepaymentWallets::getWalletInstance((int)$context->cart->id_customer))) {
            return;
        }

        $sql = '
		SELECT pg.*
		FROM '._DB_PREFIX_.'prepayment_gifts pg
		LEFT JOIN '._DB_PREFIX_.'prepayment_gifts_shop pgs ON pg.id_prepayment_gifts = pgs.id_prepayment_gifts
		LEFT JOIN '._DB_PREFIX_.'prepayment_gifts_payment pgp ON pg.id_prepayment_gifts = pgp.id_prepayment_gifts
		LEFT JOIN '._DB_PREFIX_.'prepayment_gifts_carrier pgca ON pg.id_prepayment_gifts = pgca.id_prepayment_gifts
		'.($context->cart->id_carrier ? 'LEFT JOIN '._DB_PREFIX_.'carrier c ON (c.id_reference = pgca.id_carrier AND c.deleted = 0)' : '').'
		LEFT JOIN '._DB_PREFIX_.'prepayment_gifts_country pgco ON pg.id_prepayment_gifts = pgco.id_prepayment_gifts
		WHERE pg.active = 1
		AND pg.quantity > 0
		AND pg.date_from < "'.date('Y-m-d H:i:s').'"
		AND pg.date_to > "'.date('Y-m-d H:i:s').'"
		AND (
			pg.id_customer = 0
			'.($context->customer->id ? 'OR pg.id_customer = '.(int)$context->cart->id_customer : '').'
		)
		AND (
			pg.`carrier_restriction` = 0
			'.($context->cart->id_carrier ? 'OR c.id_carrier = '.(int)$context->cart->id_carrier : '').'
		)
		AND (
			pg.`shop_restriction` = 0
			'.((Shop::isFeatureActive() && $context->shop->id) ? 'OR pgs.id_shop = '.(int)$context->shop->id : '').'
		)
		AND (
			pg.`payment_restriction` = 0
			'.($order ? 'OR pgp.id_module = '.(int)Module::getModuleIdByName($order->module) : '').'
		)
		AND (
			pg.`group_restriction` = 0
			'.($context->customer->id ? 'OR 0 < (
				SELECT cg.`id_group`
				FROM `'._DB_PREFIX_.'customer_group` cg
				INNER JOIN `'._DB_PREFIX_.'prepayment_gifts_group` pgg ON cg.id_group = pgg.id_group
				WHERE pg.`id_prepayment_gifts` = pgg.`id_prepayment_gifts`
				AND cg.`id_customer` = '.(int)$context->customer->id.'
				LIMIT 1
			)' : '').'
		)
		ORDER BY priority';
        $result = Db::getInstance()->executeS($sql);
        if ($result) {
            $gifts = ObjectModel::hydrateCollection('PrepaymentGifts', $result);
            if ($gifts) {
                foreach ($gifts as $gift) {
                    if (Validate::isLoadedObject($gift) && $gift->checkValidity($order, $context, false, false)) {
                        return $gift;
                    }
                }
            }
        }
        return false;
    }

    protected function checkProductRestrictions(Context $context, $return_products = false, $display_error = true, $already_in_cart = false)
    {
        $selected_products = array();

        // Check if the products chosen by the customer are usable with the cart rule
        if ($this->product_restriction) {
            $product_rule_groups = $this->getProductRuleGroups();
            foreach ($product_rule_groups as $id_product_rule_group => $product_rule_group) {
                $eligible_products_list = array();
                foreach ($context->cart->getProducts() as $product) {
                    $eligible_products_list[] = (int)$product['id_product'].'-'.(int)$product['id_product_attribute'];
                }
                if (!count($eligible_products_list)) {
                    return (!$display_error) ? false : Tools::displayError('You cannot use this voucher in an empty cart');
                }

                $product_rules = $this->getProductRules($id_product_rule_group);
                foreach ($product_rules as $product_rule) {
                    switch ($product_rule['type']) {
                        case 'attributes':
                            $cart_attributes = Db::getInstance()->executeS('
							SELECT cp.quantity, cp.`id_product`, pac.`id_attribute`, cp.`id_product_attribute`
							FROM `'._DB_PREFIX_.'cart_product` cp
							LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON cp.id_product_attribute = pac.id_product_attribute
							WHERE cp.`id_cart` = '.(int)$context->cart->id.'
							AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')
							AND cp.id_product_attribute > 0');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_attributes as $cart_attribute) {
                                if (in_array($cart_attribute['id_attribute'], $product_rule['values'])) {
                                    $count_matching_products += $cart_attribute['quantity'];
                                    if ($already_in_cart) {
                                        --$count_matching_products;
                                    }
                                    $matching_products_list[] = $cart_attribute['id_product'].'-'.$cart_attribute['id_product_attribute'];
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with these products');
                            }
                            $eligible_products_list = PrepaymentGifts::arrayUintersect($eligible_products_list, $matching_products_list);
                            break;
                        case 'products':
                            $cart_products = Db::getInstance()->executeS('
							SELECT cp.quantity, cp.`id_product`
							FROM `'._DB_PREFIX_.'cart_product` cp
							WHERE cp.`id_cart` = '.(int)$context->cart->id.'
							AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_products as $cart_product) {
                                if (in_array($cart_product['id_product'], $product_rule['values'])) {
                                    $count_matching_products += $cart_product['quantity'];
                                    if ($already_in_cart && $this->gift_product == $cart_product['id_product']) {
                                        --$count_matching_products;
                                    }
                                    $matching_products_list[] = $cart_product['id_product'].'-0';
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with these products');
                            }
                            $eligible_products_list = PrepaymentGifts::arrayUintersect($eligible_products_list, $matching_products_list);
                            break;
                        case 'categories':
                            $cart_categories = Db::getInstance()->executeS('
							SELECT cp.quantity, cp.`id_product`, cp.`id_product_attribute`, catp.`id_category`
							FROM `'._DB_PREFIX_.'cart_product` cp
							LEFT JOIN `'._DB_PREFIX_.'category_product` catp ON cp.id_product = catp.id_product
							WHERE cp.`id_cart` = '.(int)$context->cart->id.'
							AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_categories as $cart_category) {
                                if (in_array($cart_category['id_category'], $product_rule['values'])
                                    // We also check that the product is not already in the matching product list, because there are doubles in the query results (when the product is in multiple categories)
                                    && !in_array($cart_category['id_product'].'-'.$cart_category['id_product_attribute'], $matching_products_list)) {
                                    $count_matching_products += $cart_category['quantity'];
                                    $matching_products_list[] = $cart_category['id_product'].'-'.$cart_category['id_product_attribute'];
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with these products');
                            }
                            // Attribute id is not important for this filter in the global list, so the ids are replaced by 0
                            foreach ($matching_products_list as &$matching_product) {
                                $matching_product = preg_replace('/^([0-9]+)-[0-9]+$/', '$1-0', $matching_product);
                            }
                            $eligible_products_list = PrepaymentGifts::arrayUintersect($eligible_products_list, $matching_products_list);
                            break;
                        case 'manufacturers':
                            $cart_manufacturers = Db::getInstance()->executeS('
							SELECT cp.quantity, cp.`id_product`, p.`id_manufacturer`
							FROM `'._DB_PREFIX_.'cart_product` cp
							LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
							WHERE cp.`id_cart` = '.(int)$context->cart->id.'
							AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_manufacturers as $cart_manufacturer) {
                                if (in_array($cart_manufacturer['id_manufacturer'], $product_rule['values'])) {
                                    $count_matching_products += $cart_manufacturer['quantity'];
                                    $matching_products_list[] = $cart_manufacturer['id_product'].'-0';
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with these products');
                            }
                            $eligible_products_list = PrepaymentGifts::arrayUintersect($eligible_products_list, $matching_products_list);
                            break;
                        case 'suppliers':
                            $cart_suppliers = Db::getInstance()->executeS('
							SELECT cp.quantity, cp.`id_product`, p.`id_supplier`
							FROM `'._DB_PREFIX_.'cart_product` cp
							LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
							WHERE cp.`id_cart` = '.(int)$context->cart->id.'
							AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_suppliers as $cart_supplier) {
                                if (in_array($cart_supplier['id_supplier'], $product_rule['values'])) {
                                    $count_matching_products += $cart_supplier['quantity'];
                                    $matching_products_list[] = $cart_supplier['id_product'].'-0';
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with these products');
                            }
                            $eligible_products_list = PrepaymentGifts::arrayUintersect($eligible_products_list, $matching_products_list);
                            break;
                    }

                    if (!count($eligible_products_list)) {
                        return (!$display_error) ? false : Tools::displayError('You cannot use this voucher with these products');
                    }
                }
                $selected_products = array_merge($selected_products, $eligible_products_list);
            }
        }

        if ($return_products) {
            return $selected_products;
        }
        return (!$display_error) ? true : false;
    }

    protected static function arrayUintersect($array1, $array2)
    {
        $intersection = array();
        foreach ($array1 as $value1) {
            foreach ($array2 as $value2) {
                if (PrepaymentGifts::arrayUintersectCompare($value1, $value2) == 0) {
                    $intersection[] = $value1;
                    break 1;
                }
            }
        }
        return $intersection;
    }

    protected static function arrayUintersectCompare($a, $b)
    {
        if ($a == $b) {
            return 0;
        }

        $asplit = explode('-', $a);
        $bsplit = explode('-', $b);
        if ($asplit[0] == $bsplit[0] && (!(int)$asplit[1] || !(int)$bsplit[1])) {
            return 0;
        }

        return 1;
    }

    public static function generateReference()
    {
        return Tools::strtoupper(Tools::passwdGen(9, 'NO_NUMERIC'));
    }

    public static function getStats()
    {
        $result = array();

        /* Get most used gift rule */
        $most_used = Db::getInstance()->getRow('
		SELECT COUNT(pg.`id_prepayment_gifts`) as nb, pgl.`name`
		FROM `'._DB_PREFIX_.'prepayment_gifts` pg
		LEFT JOIN `'._DB_PREFIX_.'prepayment_gifts_lang` pgl on pg.`id_prepayment_gifts` = pgl.`id_prepayment_gifts`
		WHERE pgl.`id_lang` = '.(int)Context::getContext()->language->id.'
		GROUP BY `reference`
		ORDER BY `nb` DESC');
        if (!$most_used) {
            $most_used = array();
        }

        /* Get average credits per gift rule and number of gifts used*/
        $gifts = Db::getInstance()->getRow('
		SELECT SUM(pla.`credits` + pla.`extra_credits`) AS nb_credit, COUNT(pla.`id_prepayment_last_activities`) AS nb_operation
		FROM `'._DB_PREFIX_.'prepayment_last_activities` pla
		WHERE pla.`id_operation` = '.PrepaymentLastActivities::GIFT.'
		HAVING COUNT(pla.`id_prepayment_last_activities`) > 0');
        if (!$gifts) {
            $credits_per_gift = 0;
            $gifts_used = 0;
        } else {
            $credits_per_gift = $gifts['nb_credit'] / $gifts['nb_operation'];
            $gifts_used = $gifts['nb_operation'];
        }

        /* Get expired gift rules*/
        $now = date('Y-m-d H:i:s');
        $expired_gifts = Db::getInstance()->getValue('
		SELECT COUNT(`id_prepayment_gifts`)
		FROM `'._DB_PREFIX_.'prepayment_gifts`
		WHERE `date_to` < "'.$now.'"');

        /* Add stats into an array */
        $result['most_used'] = $most_used;
        $result['credits_per_gift'] = Tools::ps_round($credits_per_gift, 2);
        $result['gifts_used'] = $gifts_used;
        $result['expired_gifts'] = $expired_gifts;

        return $result;
    }
}
