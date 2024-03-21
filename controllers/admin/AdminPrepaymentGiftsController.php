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

class AdminPrepaymentGiftsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'prepayment_gifts';
        $this->className = 'PrepaymentGifts';
        $this->lang = true;

        parent::__construct();

        $this->fields_list = array(
            'id_prepayment_gifts' => array('title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'name' => array('title' => $this->l('Name')),
            'priority' => array('title' => $this->l('Priority'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'reference' => array('title' => $this->l('Reference'), 'class' => 'fixed-width-sm'),
            'quantity' => array('title' => $this->l('Quantity'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'date_to' => array('title' => $this->l('Expiration date'), 'type' => 'datetime'),
            'active' => array('title' => $this->l('Status'), 'active' => 'status', 'type' => 'bool', 'orderby' => false),
        );
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJqueryPlugin(array('typewatch', 'fancybox', 'autocomplete'));
        $this->addJqueryUI('ui.datepicker');
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addJS(_MODULE_DIR_.'prepayment/views/js/tools/bootstrap.js');
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();

        if ($this->display == 'edit' || $this->display == 'add') {
            $this->toolbar_btn['save-and-stay'] = array(
                'href' => '#',
                'desc' => $this->l('Save and Stay')
            );
        }
        $this->toolbar_btn['back'] = array(
            'href' =>  $this->context->link->getAdminLink('AdminPrepaymentDashboard'),
            'desc' => $this->l('Back to list')
        );
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->l('Gift rules');
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminPrepaymentDashboard'),
            'desc' => $this->l('Back', null, null, false),
            'icon' => 'process-icon-back'
        );
        $this->page_header_toolbar_btn['new_gift'] = array(
            'href' => self::$currentIndex.'&addprepayment_gifts&token='.$this->token,
            'desc' => $this->l('Add new gift rule', null, null, false),
            'icon' => 'process-icon-new'
        );

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->_orderWay = 'DESC';
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );

        return parent::renderList();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddprepayment_gifts') || Tools::isSubmit('submitAddprepayment_giftsAndStay')) {
            // These are checkboxes (which aren't sent through POST when they are not check), so they are forced to 0
            foreach (array('country', 'carrier', 'group', 'product', 'shop', 'payment') as $type) {
                if (!Tools::getValue($type.'_restriction')) {
                    $_POST[$type.'_restriction'] = 0;
                }
            }

            $_POST['reference'] = PrepaymentGifts::generateReference();

            // Idiot-proof control
            if (strtotime(Tools::getValue('date_from')) > strtotime(Tools::getValue('date_to'))) {
                $this->errors[] = Tools::displayError('The voucher cannot end before it begins.');
            }
            if ((int)Tools::getValue('minimum_amount') < 0) {
                $this->errors[] = Tools::displayError('The minimum amount cannot be lower than zero.');
            }
            if ((float)Tools::getValue('reduction_percent') < 0 || (float)Tools::getValue('reduction_percent') > 100) {
                $this->errors[] = Tools::displayError('Reduction percentage must be between 0% and 100%');
            }
            if ((int)Tools::getValue('reduction_amount') < 0) {
                $this->errors[] = Tools::displayError('Reduction amount cannot be lower than zero.');
            }
            if (Tools::getValue('code') && ($same_code = (int)CartRule::getIdByCode(Tools::getValue('code'))) && $same_code != Tools::getValue('id_cart_rule')) {
                $this->errors[] = sprintf(Tools::displayError('This cart rule code is already used (conflict with cart rule %d)'), $same_code);
            }
            if (Tools::getValue('apply_discount') == 'off' && !Tools::getValue('free_shipping') && !Tools::getValue('free_gift')) {
                $this->errors[] = Tools::displayError('An action is required for this cart rule.');
            }
        }

        return parent::postProcess();
    }

    protected function afterUpdate($current_object)
    {
        // All the associations are deleted for an update, then recreated when we call the "afterAdd" method
        $id_prepayment_gifts = Tools::getValue('id_prepayment_gifts');
        foreach (array('country', 'carrier', 'group', 'product_rule_group', 'shop', 'payment') as $type) {
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_'.$type.'` WHERE `id_prepayment_gifts` = '.(int)$id_prepayment_gifts);
        }
        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule` WHERE `id_product_rule_group`
			NOT IN (SELECT `id_product_rule_group` FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule_group`)');
        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule_value` WHERE `id_product_rule`
			NOT IN (SELECT `id_product_rule` FROM `'._DB_PREFIX_.'prepayment_gifts_product_rule`)');

        $this->afterAdd($current_object);
    }

    public function processAdd()
    {
        if ($prepayment_gifts = parent::processAdd()) {
            $this->context->smarty->assign('new_prepayment_gifts', $prepayment_gifts);
        }
        if (Tools::getValue('submitFormAjax')) {
            $this->redirect_after = false;
        }

        return $prepayment_gifts;
    }

    protected function afterAdd($current_object)
    {
        // Add restrictions for generic entities like country, carrier and group
        foreach (array('country', 'carrier', 'group', 'shop', 'payment') as $type) {
            if (Tools::getValue($type.'_restriction') && is_array($array = Tools::getValue($type.'_select')) && count($array)) {
                $values = array();
                foreach ($array as $id) {
                    $values[] = '('.(int)$current_object->id.','.(int)$id.')';
                }
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'prepayment_gifts_'.$type.'` (`id_prepayment_gifts`, `id_'.($type == 'payment' ? 'module' : $type).'`) VALUES '.implode(',', $values)
                );
            }
        }

        // Add product rule restrictions
        if (Tools::getValue('product_restriction') && is_array($rule_group_array = Tools::getValue('product_rule_group')) && count($rule_group_array)) {
            foreach ($rule_group_array as $rule_group_id) {
                Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'prepayment_gifts_product_rule_group` (`id_prepayment_gifts`, `quantity`)
				VALUES ('.(int)$current_object->id.', '.(int)Tools::getValue('product_rule_group_'.$rule_group_id.'_quantity').')');
                $id_product_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($rule_array = Tools::getValue('product_rule_'.$rule_group_id)) && count($rule_array)) {
                    foreach ($rule_array as $rule_id) {
                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'prepayment_gifts_product_rule` (`id_product_rule_group`, `type`)
						VALUES ('.(int)$id_product_rule_group.', "'.pSQL(Tools::getValue('product_rule_'.$rule_group_id.'_'.$rule_id.'_type')).'")');
                        $id_product_rule = Db::getInstance()->Insert_ID();

                        $values = array();
                        foreach (Tools::getValue('product_rule_select_'.$rule_group_id.'_'.$rule_id) as $id) {
                            $values[] = '('.(int)$id_product_rule.','.(int)$id.')';
                        }
                        $values = array_unique($values);
                        if (count($values)) {
                            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'prepayment_gifts_product_rule_value` (`id_product_rule`, `id_item`) VALUES '.implode(',', $values));
                        }
                    }
                }
            }
        }
    }

    public function getProductRuleGroupsDisplay($gifts)
    {
        $product_rule_groups_array = array();
        if (Tools::getValue('product_restriction') && is_array($array = Tools::getValue('product_rule_group')) && count($array)) {
            $i = 1;
            foreach ($array as $rule_group_id) {
                $product_rules_array = array();
                if (is_array($array = Tools::getValue('product_rule_'.$rule_group_id)) && count($array)) {
                    foreach ($array as $rule_id) {
                        $product_rules_array[] = $this->getProductRuleDisplay(
                            $rule_group_id,
                            $rule_id,
                            Tools::getValue('product_rule_'.$rule_group_id.'_'.$rule_id.'_type'),
                            Tools::getValue('product_rule_select_'.$rule_group_id.'_'.$rule_id)
                        );
                    }
                }

                $product_rule_groups_array[] = $this->getProductRuleGroupDisplay(
                    $i++,
                    (int)Tools::getValue('product_rule_group_'.$rule_group_id.'_quantity'),
                    $product_rules_array
                );
            }
        } else {
            $i = 1;
            foreach ($gifts->getProductRuleGroups() as $product_rule_group) {
                $j = 1;
                $product_rules_display = array();
                foreach ($product_rule_group['product_rules'] as $product_rule) {
                    $product_rules_display[] = $this->getProductRuleDisplay($i, $j++, $product_rule['type'], $product_rule['values']);
                }
                $product_rule_groups_array[] = $this->getProductRuleGroupDisplay($i++, $product_rule_group['quantity'], $product_rules_display);
            }
        }
        return $product_rule_groups_array;
    }

    public function getProductRuleDisplay($product_rule_group_id, $product_rule_id, $product_rule_type, $selected = array())
    {
        Context::getContext()->smarty->assign(
            array(
                'product_rule_group_id' => (int)$product_rule_group_id,
                'product_rule_id' => (int)$product_rule_id,
                'product_rule_type' => $product_rule_type,
            )
        );

        switch ($product_rule_type) {
            case 'attributes':
                $attributes = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT CONCAT(agl.name, " - ", al.name) as name, a.id_attribute as id
				FROM '._DB_PREFIX_.'attribute_group_lang agl
				LEFT JOIN '._DB_PREFIX_.'attribute a ON a.id_attribute_group = agl.id_attribute_group
				LEFT JOIN '._DB_PREFIX_.'attribute_lang al ON (a.id_attribute = al.id_attribute AND al.id_lang = '.(int)Context::getContext()->language->id.')
				WHERE agl.id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY agl.name, al.name');
                foreach ($results as $row) {
                    $attributes[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                Context::getContext()->smarty->assign(
                    'product_rule_itemlist',
                    $attributes
                );
                $choose_content = Context::getContext()->smarty->createTemplate(
                    _PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/product_rule_itemlist.tpl',
                    Context::getContext()->smarty
                )->fetch();
                Context::getContext()->smarty->assign(
                    'product_rule_choose_content',
                    $choose_content
                );
                break;
            case 'products':
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT DISTINCT name, p.id_product as id
				FROM '._DB_PREFIX_.'product p
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON (p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('pl').')
				'.Shop::addSqlAssociation('product', 'p').'
				WHERE id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY name');
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                Context::getContext()->smarty->assign(
                    'product_rule_itemlist',
                    $products
                );
                $choose_content = Context::getContext()->smarty->createTemplate(
                    _PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/product_rule_itemlist.tpl',
                    Context::getContext()->smarty
                )->fetch();
                Context::getContext()->smarty->assign(
                    'product_rule_choose_content',
                    $choose_content
                );
                break;
            case 'manufacturers':
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT name, id_manufacturer as id
				FROM '._DB_PREFIX_.'manufacturer
				ORDER BY name');
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                Context::getContext()->smarty->assign(
                    'product_rule_itemlist',
                    $products
                );
                $choose_content = Context::getContext()->smarty->createTemplate(
                    _PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/product_rule_itemlist.tpl',
                    Context::getContext()->smarty
                )->fetch();
                Context::getContext()->smarty->assign(
                    'product_rule_choose_content',
                    $choose_content
                );
                break;
            case 'suppliers':
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT name, id_supplier as id
				FROM '._DB_PREFIX_.'supplier
				ORDER BY name');
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                Context::getContext()->smarty->assign(
                    'product_rule_itemlist',
                    $products
                );
                $choose_content = Context::getContext()->smarty->createTemplate(
                    _PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/product_rule_itemlist.tpl',
                    Context::getContext()->smarty
                )->fetch();
                Context::getContext()->smarty->assign(
                    'product_rule_choose_content',
                    $choose_content
                );
                break;
            case 'categories':
                $categories = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT DISTINCT name, c.id_category as id
				FROM '._DB_PREFIX_.'category c
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
					ON (c.`id_category` = cl.`id_category`
					AND cl.`id_lang` = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('cl').')
				'.Shop::addSqlAssociation('category', 'c').'
				WHERE id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY name');
                foreach ($results as $row) {
                    $categories[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                Context::getContext()->smarty->assign(
                    'product_rule_itemlist',
                    $categories
                );
                $choose_content = Context::getContext()->smarty->createTemplate(
                    _PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/product_rule_itemlist.tpl',
                    Context::getContext()->smarty
                )->fetch();
                Context::getContext()->smarty->assign(
                    'product_rule_choose_content',
                    $choose_content
                );
                break;
            default:
                Context::getContext()->smarty->assign(array(
                    'product_rule_itemlist' => array('selected' => array(), 'unselected' => array()),
                    'product_rule_choose_content' => ''
                ));
        }

        return Context::getContext()->smarty->createTemplate(
            _PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/product_rule.tpl',
            Context::getContext()->smarty
        )->fetch();
    }

    public function getProductRuleGroupDisplay($product_rule_group_id, $product_rule_group_quantity = 1, $product_rules = null)
    {
        Context::getContext()->smarty->assign(array(
            'product_rule_group_id' => $product_rule_group_id,
            'product_rule_group_quantity' => $product_rule_group_quantity,
            'product_rules' => $product_rules
        ));

        return Context::getContext()->smarty->createTemplate(
            _PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/product_rule_group.tpl',
            Context::getContext()->smarty
        )->fetch();
    }

    public function renderForm()
    {
        $current_object = $this->loadObject(true);

        // All the filter are prefilled with the correct information
        $customer_filter = '';
        if (Validate::isUnsignedId($current_object->id_customer)
        && ($customer = new Customer($current_object->id_customer))
        && Validate::isLoadedObject($customer)) {
            $customer_filter = $customer->firstname.' '.$customer->lastname.' ('.$customer->email.')';
        }

        $product_rule_groups = $this->getProductRuleGroupsDisplay($current_object);

        $attribute_groups = AttributeGroup::getAttributesGroups($this->context->language->id);
        $currencies = Currency::getCurrencies();
        $languages = Language::getLanguages();
        $countries = $current_object->getAssociatedRestrictions('country', true, true);
        $groups = $current_object->getAssociatedRestrictions('group', false, true);
        $shops = $current_object->getAssociatedRestrictions('shop', false, false);
        $payments = $current_object->getAssociatedRestrictions('payment', false, false);
        $carriers = $current_object->getAssociatedRestrictions('carrier', true, false);
        foreach ($carriers as &$carriers2) {
            foreach ($carriers2 as &$carrier) {
                foreach ($carrier as $field => &$value) {
                    if ($field == 'name' && $value == '0') {
                        $value = Configuration::get('PS_SHOP_NAME');
                    }
                }
            }
        }

        $this->context->smarty->assign(
            array(
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'toolbar_scroll' => $this->toolbar_scroll,
                'title' => array($this->l('Payment:'), $this->l('Gift rules')),
                'tpl_informations' => _PS_ROOT_DIR_.'/modules/prepayment/views/templates/admin/prepayment_gifts/informations.tpl',
                'tpl_conditions' => _PS_ROOT_DIR_.'/modules/prepayment/views/templates/admin/prepayment_gifts/conditions.tpl',
                'tpl_actions' => _PS_ROOT_DIR_.'/modules/prepayment/views/templates/admin/prepayment_gifts/actions.tpl',
                'defaultDateFrom' => date('Y-m-d H:00:00'),
                'defaultDateTo' => date('Y-m-d H:00:00', strtotime('+1 month')),
                'customerFilter' => $customer_filter,
                'defaultCurrency' => Configuration::get('PS_CURRENCY_DEFAULT'),
                'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
                'languages' => $languages,
                'currencies' => $currencies,
                'countries' => $countries,
                'carriers' => $carriers,
                'groups' => $groups,
                'shops' => $shops,
                'payments' => $payments,
                'product_rule_groups' => $product_rule_groups,
                'product_rule_groups_counter' => count($product_rule_groups),
                'attribute_groups' => $attribute_groups,
                'currentIndex' => self::$currentIndex,
                'currentToken' => $this->token,
                'currentObject' => $current_object,
                'currentTab' => $this,
            )
        );

        $this->content .= $this->context->smarty->createTemplate(_PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_gifts/form.tpl', $this->context->smarty)->fetch();
        return parent::renderForm();
    }

    public function ajaxProcess()
    {
        if (Tools::isSubmit('newProductRule')) {
            die($this->getProductRuleDisplay(Tools::getValue('product_rule_group_id'), Tools::getValue('product_rule_id'), Tools::getValue('product_rule_type')));
        }
        if (Tools::isSubmit('newProductRuleGroup') && $product_rule_group_id = Tools::getValue('product_rule_group_id')) {
            die($this->getProductRuleGroupDisplay($product_rule_group_id, Tools::getValue('product_rule_group_'.$product_rule_group_id.'_quantity', 1)));
        }

        if (Tools::isSubmit('customerFilter')) {
            $search_query = trim(Tools::getValue('q'));
            $customers = Db::getInstance()->executeS('
			SELECT `id_customer`, `email`, CONCAT(`firstname`, \' \', `lastname`) as cname
			FROM `'._DB_PREFIX_.'customer`
			WHERE `deleted` = 0 AND is_guest = 0 AND active = 1
			AND (
				`id_customer` = '.(int)$search_query.'
				OR `email` LIKE "%'.pSQL($search_query).'%"
				OR `firstname` LIKE "%'.pSQL($search_query).'%"
				OR `lastname` LIKE "%'.pSQL($search_query).'%"
			)
			ORDER BY `firstname`, `lastname` ASC
			LIMIT 50');
            die(Tools::jsonEncode($customers));
        }
        // Both product filter (free product and product discount) search for products
        if (Tools::isSubmit('giftProductFilter') || Tools::isSubmit('reductionProductFilter')) {
            $products = Product::searchByName(Context::getContext()->language->id, trim(Tools::getValue('q')));
            die(Tools::jsonEncode($products));
        }
    }
}
