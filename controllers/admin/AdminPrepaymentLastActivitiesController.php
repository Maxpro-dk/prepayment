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

class AdminPrepaymentLastActivitiesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'prepayment_last_activities';
        $this->className = 'PrepaymentLastActivities';
        $this->explicitSelect = true;
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();

        $this->fields_list = array(
            'id_prepayment_last_activities' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
            ),
            'operation' => array(
                'title' => $this->l('Operation'),
                'havingFilter' => true,
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'havingFilter' => true,
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
            'price' => array(
                'title' => $this->l('Order Price'),
                'align' => 'text-right',
                'callback' => 'setCurrency',
                'badge_success' => true
            ),
            'credits' => array(
                'title' => $this->l('Credits'),
                'align' => 'text-right',
                'callback' => 'setPacksCurrency',
                'badge_success' => true
            ),
            'extra_credits' => array(
                'title' => $this->l('Extra Credits'),
                'align' => 'text-right',
                'callback' => 'setPacksCurrency',
                'badge_success' => true
            ),
            'paid' => array(
                'title' => $this->l('Paid'),
                'align' => 'text-center',
                'active' => 'paid',
                'type' => 'bool',
                'ajax' => false,
                'orderby' => false,
                'filter_key' => 'a!paid',
                'class' => 'fixed-width-sm'
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            )
        );
    }

    public function setCurrency($value, $tr)
    {
        $last_activities = new PrepaymentLastActivities($tr['id_prepayment_last_activities']);
        if (!Validate::isLoadedObject($last_activities)) {
            throw new PrestaShopException('object Last Activities can\'t be loaded');
        }

        return Tools::displayPrice($value, Currency::getCurrencyInstance($last_activities->id_currency));
    }

    public function setPacksCurrency($value, $tr)
    {
        $last_activities = new PrepaymentLastActivities($tr['id_prepayment_last_activities']);
        if (!Validate::isLoadedObject($last_activities)) {
            throw new PrestaShopException('object Last Activities can\'t be loaded');
        }

        $from_currency = Currency::getCurrencyInstance((int)$last_activities->id_currency);
        $to_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        return Tools::displayPrice(Tools::convertPriceFull($value, $from_currency, $to_currency), $to_currency->id);
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
        $this->page_header_toolbar_title = $this->l('Last Activities');
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminPrepaymentDashboard'),
            'desc' => $this->l('Back', null, null, false),
            'icon' => 'process-icon-back'
        );
        $this->page_header_toolbar_btn['new_operation'] = array(
            'href' => $this->context->link->getAdminLink('AdminPrepaymentLastActivities').'&addprepayment_last_activities',
            'desc' => $this->l('Add new movement', null, null, false),
            'icon' => 'process-icon-new'
        );

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->_select = '
		c.`email` AS `customer`,
		IF(a.`paid`, 1, 0) badge_success,
		a.`id_operation` as operation,
		CASE id_operation
			WHEN \''.PrepaymentLastActivities::DEPOSIT.'\' THEN \''.$this->l('Deposit').'\'
			WHEN \''.PrepaymentLastActivities::ORDER.'\' THEN \''.$this->l('Order').'\'
			WHEN \''.PrepaymentLastActivities::REFUND.'\' THEN \''.$this->l('Refund').'\'
			WHEN \''.PrepaymentLastActivities::DISBURSEMENT.'\' THEN \''.$this->l('Disbursement').'\'
			WHEN \''.PrepaymentLastActivities::GIFT.'\' THEN \''.$this->l('Gift').'\'
			WHEN \''.PrepaymentLastActivities::CUSTOM_DEPOSIT.'\' THEN \''.$this->l('Manual deposit').'\'
			WHEN \''.PrepaymentLastActivities::CUSTOM_DISBURSEMENT.'\' THEN \''.$this->l('Manual disbursement').'\'
		END operation';

        $this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)';
        $this->_orderBy = 'id_prepayment_last_activities';
        $this->_orderWay = 'DESC';

        return parent::renderList();
    }

    public function renderView()
    {
        $id_last_activities = (int)Tools::getValue('id_prepayment_last_activities');

        if (!$id_last_activities && Validate::isLoadedObject($this->object)) {
            $id_last_activities = $this->object->id_prepayment_last_activities;
        }
        if ($id_last_activities) {
            $last_activities = new PrepaymentLastActivities((int)$id_last_activities);
            if ($last_activities->id_operation == PrepaymentLastActivities::CUSTOM_DEPOSIT || $last_activities->id_operation == PrepaymentLastActivities::CUSTOM_DISBURSEMENT) {
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminPrepaymentLastActivities').'&id_prepayment_last_activities='.$last_activities->id.'&updateprepayment_last_activities');
            } elseif ($last_activities->id_operation == PrepaymentLastActivities::ORDER && PrepaymentLastActivities::isPartial($last_activities->id)) {
                $id_partial = $last_activities->getPartialId();
                $partial = new PrepaymentPartials((int)$id_partial);
                if (!Validate::isLoadedObject($partial)) {
                    $this->errors[] = Tools::displayError('Unable to load the related cart');
                } else {
                    $token_cart = Tools::getAdminToken('AdminCarts'.(int)Tab::getIdFromClassName('AdminCarts').(int)$this->context->employee->id);
                    Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminCarts').'&id_cart='.$partial->id_cart.'&viewcart');
                }
            } else {
                $token_order = Tools::getAdminToken('AdminOrders'.(int)Tab::getIdFromClassName('AdminOrders').(int)$this->context->employee->id);
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders').'&id_order='.$last_activities->id_order.'&vieworder');
            }
        }
        return parent::renderView();
    }

    public function renderForm()
    {
        $current_object = $this->loadObject(true);

        $customer = new Customer((int)$current_object->id_customer);
        $email = Validate::isLoadedObject($customer) ? $customer->email : '';

        $this->context->smarty->assign(array(
            'customerEmail' => $email,
            'currentObj' => $current_object,
            'currencyObj' => $this->context->currency,
            'currentIndex' => self::$currentIndex,
            'currentToken' => $this->token,
        ));

        $this->addJqueryPlugin('typewatch');

        $this->content .= $this->context->smarty->createTemplate(_PS_MODULE_DIR_.'prepayment/views/templates/admin/prepayment_last_activities/form.tpl', $this->context->smarty)->fetch();
        return parent::renderForm();
    }

    public function ajaxProcessSearchCustomer()
    {
        // Get the search pattern
        $pattern = pSQL(Tools::getValue('q', false));

        if (!$pattern || $pattern == '' || Tools::strlen($pattern) < 1) {
            die();
        }

        $query = new DbQuery();
        $query->select('
			CONCAT(c.firstname, \'_\', c.lastname) as name,
			IFNULL(c.email, \'\') as email,
			IFNULL(c.id_customer, \'\') as id
		');

        $query->from('customer', 'c');
        $query->where('(c.firstname LIKE \'%'.$pattern.'%\' OR c.lastname LIKE \'%'.$pattern.'%\' OR c.email LIKE \'%'.$pattern.'%\')');

        $items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($items) {
            die(Tools::jsonEncode($items));
        }

        die(1);
    }

    public function ajaxProcessSearchLastActivities()
    {
        $id_customer = (int)Tools::getValue('id_customer');
        $last_activities = PrepaymentLastActivities::getLastActivities($id_customer);
        foreach ($last_activities as &$last_activity) {
            if ($last_activity['id_operation'] == PrepaymentLastActivities::DEPOSIT) {
                $last_activity['operation'] = $this->l('Deposit');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::ORDER) {
                $last_activity['operation'] = $this->l('Order');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::REFUND) {
                $last_activity['operation'] = $this->l('Refund');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::DISBURSEMENT) {
                $last_activity['operation'] = $this->l('Disbursement');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::GIFT) {
                $last_activity['operation'] = $this->l('Gift');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::CUSTOM_DEPOSIT) {
                $last_activity['operation'] = $this->l('Manual deposit');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::CUSTOM_DISBURSEMENT) {
                $last_activity['operation'] = $this->l('Manual disbursement');
            }

            $last_activity['price'] = Tools::displayPrice($last_activity['price'], Currency::getCurrencyInstance($last_activity['id_currency']));

            $credits = Tools::convertPriceFull($last_activity['credits'] + $last_activity['extra_credits'], Currency::getCurrencyInstance($last_activity['id_currency']), Context::getContext()->currency);
            $last_activity['credits'] = Tools::displayPrice($credits, Context::getContext()->currency);

            $last_activity['status'] = (bool)$last_activity['paid'] ? '<i class="icon-check"></i>' : '<i class="icon-times"></i>';
        }

        $to_return = array(
            'found' => true,
            'last_activities' => $last_activities,
        );

        die(Tools::jsonEncode($to_return));
    }

    public function postProcess()
    {
        if (Tools::isSubmit('delete'.$this->table)) {
            if ($this->tabAccess['delete'] == '1') {
                $id = (int)Tools::getValue('id_'.$this->table);
                $last_activities = new PrepaymentLastActivities($id);
                if (Validate::isLoadedObject($last_activities)) {
                    $last_activities->delete();
                    Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token);
                } else {
                    $this->errors[] = Tools::displayError('An error occurred while deleting an object.').' <b>'.$this->table.'</b>';
                }
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
            }
        } elseif (Tools::isSubmit($this->table.'Box') && count(Tools::isSubmit($this->table.'Box')) > 0) {
            if ($this->tabAccess['delete'] == '1') {
                $ids = Tools::getValue($this->table.'Box');
                array_walk($ids, 'intval');
                foreach ($ids as $id) {
                    $last_activities = new PrepaymentLastActivities((int)$id);
                    $last_activities->delete();
                }
                Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token);
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
            }
        } elseif (Tools::isSubmit('submitAdd'.$this->table) || Tools::isSubmit('submitUpdate'.$this->table)) {
            $id_prepayment_last_activities = (int)Tools::getValue('id_prepayment_last_activities');
            $id_customer = (int)Tools::getValue('id_customer');
            $type_movement = (int)Tools::getValue('type_movement');
            $label = Tools::getValue('label');
            $amount = (float)Tools::getValue('amount');
            $id_lang = (int)Context::getContext()->language->id;

            // Idiot-proof control
            if ($type_movement !== PrepaymentLastActivities::CUSTOM_DEPOSIT && $type_movement !== PrepaymentLastActivities::CUSTOM_DISBURSEMENT) {
                $this->errors[] = Tools::displayError('Please select a valid movement type');
            }
            if (!Validate::isCleanHtml($label)) {
                $this->errors[] = Tools::displayError('Unvalid label field');
            }
            if (!Validate::isPrice($amount)) {
                $this->errors[] = Tools::displayError('Unvalid amount field');
            }

            if (!count($this->errors)) {
                if (!($wallet = PrepaymentWallets::getWalletInstance((int)$id_customer))) {
                    $wallet = new PrepaymentWallets();
                    $wallet->id_customer = (int)$id_customer;
                    $wallet->add();
                }

                $last_activity = new PrepaymentLastActivities($id_prepayment_last_activities);
                $last_activity->id_order = 0;
                $last_activity->id_wallet = (int)$wallet->id;
                $last_activity->id_currency = Context::getContext()->currency->id;
                $last_activity->id_customer = (int)$id_customer;
                $last_activity->price = 0;
                $last_activity->credits = $amount;
                $last_activity->extra_credits = 0;
                $last_activity->id_operation = $type_movement;
                $last_activity->paid = 1;

                if ($last_activity->save()) {
                    Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'prepayment_last_activities_lang` WHERE `id_prepayment_last_activities` = '.(int)$last_activity->id);
                    Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'prepayment_last_activities_lang` (`id_prepayment_last_activities`, `id_lang`, `label`)
						VALUES ('.(int)$last_activity->id.', '.$id_lang.', "'.$label.'")');

                    Tools::redirectAdmin(self::$currentIndex.'&id_'.$this->table.'='.$last_activity->id.'&conf=3&token='.$this->token);
                } else {
                    $this->errors[] = Tools::displayError('An error occurred while saving an object.').' <b>'.$this->table.'</b>';
                }
            }
        }
        return parent::postProcess();
    }
}
