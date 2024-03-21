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

class AdminPrepaymentWalletsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->explicitSelect = true;
        $this->table = 'prepayment_wallets';
        $this->className = 'PrepaymentWallets';
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();

        $this->fields_list = array(
            'id_prepayment_wallets' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
            'total_amount' => array(
                'title' => $this->l('Current balance'),
                'type' => 'price',
                'badge_success' => true,
                'callback' => 'setPacksCurrency'
            ),
            'total_orders_amount' => array(
                'title' => $this->l('Orders'),
                'type' => 'price',
                'callback' => 'setPacksCurrency'
            ),
            'total_deposits_amount' => array(
                'title' => $this->l('Deposits'),
                'type' => 'price',
                'callback' => 'setPacksCurrency'
            ),
            'total_gifts_amount' => array(
                'title' => $this->l('Gifts'),
                'type' => 'price',
                'callback' => 'setPacksCurrency'
            ),
            'total_refunds_amount' => array(
                'title' => $this->l('Refunds'),
                'type' => 'price',
                'callback' => 'setPacksCurrency'
            ),
            'total_disbursements_amount' => array(
                'title' => $this->l('Disbursements'),
                'type' => 'price'
            ),
            'date_upd' => array(
                'title' => $this->l('Last operation datetime'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            ),
            'active' => array(
                'title' => $this->l('Enabled'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!active'
            )
        );
    }

    public function setPacksCurrency($value, $tr)
    {
        $wallet = new PrepaymentWallets($tr['id_prepayment_wallets']);
        if (!Validate::isLoadedObject($wallet)) {
            throw new PrestaShopException('object Wallet can\'t be loaded');
        }

        $id_currency_default = (int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS');

        return Tools::displayPrice($value, Currency::getCurrencyInstance($id_currency_default));
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
        $this->page_header_toolbar_title = $this->l('Wallets');
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminPrepaymentDashboard'),
            'desc' => $this->l('Back', null, null, false),
            'icon' => 'process-icon-back'
        );
        $this->page_header_toolbar_btn['new_wallet'] = array(
            'href' => self::$currentIndex.'&addprepayment_wallets&token='.$this->token,
            'desc' => $this->l('Add new wallet', null, null, false),
            'icon' => 'process-icon-new'
        );

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('view');
        $this->bulk_actions = false;
        $this->_select = '
		c.`email` AS `customer`,
		IF(a.active, 1, 0) badge_success';
        $this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)';

        return parent::renderList();
    }

    public function renderForm()
    {
        $this->addJqueryPlugin('autocomplete');

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Add new wallet'),
                'icon' => 'icon-truck'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Search a Customer'),
                    'name' => 'customer',
                    'required' => true,
                    'autocomplete'=> false,
                    'col' => '6'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'ids_customer',
                ),
                array(
                    'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                    'label' => $this->l('Enabled'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'hint' => $this->l('Enable the balance in the Front Office.')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        return parent::renderForm();
    }

    public function renderView()
    {
        $id_wallet = (int)Tools::getValue('id_prepayment_wallets');

        if (!$id_wallet && Validate::isLoadedObject($this->object)) {
            $id_wallet = $this->object->id_prepayment_wallets;
        }
        if ($id_wallet) {
            $wallet = new PrepaymentWallets((int)$id_wallet);
            $customer = new Customer((int)$wallet->id_customer);
            $token_customer = Tools::getAdminToken('AdminCustomers'.(int)Tab::getIdFromClassName('AdminCustomers').(int)$this->context->employee->id);
        }

        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminCustomers').'&id_customer='.$customer->id.'&viewcustomer&token='.$token_customer);

        return parent::renderView();
    }

    public function ajaxProcessUpdateBalanceWallet()
    {
        $wallet = new PrepaymentWallets((int)Tools::getValue('id_wallet'));
        if (PrepaymentLastActivities::updateWallet($wallet)) {
            die(Tools::jsonEncode(array('errors' => false, 'success' => 'ok', 'result' => $this->l('The status has been updating'))));
        } else {
            $this->content = Tools::jsonEncode(array('errors' => true, 'result' => $this->l('Error in updating customer wallet status.')));
        }
    }

    public function ajaxProcessUpdateStatusWallet()
    {
        $wallet = new PrepaymentWallets((int)Tools::getValue('id_wallet'));
        $status = (int)Tools::getValue('status');

        if (Validate::isLoadedObject($wallet)) {
            $wallet->active = $status;
            $wallet->update();
            die(Tools::jsonEncode(array('errors' => false, 'success' => 'ok', 'result' => $this->l('The status has been updating'))));
        } else {
            $this->content = Tools::jsonEncode(array('errors' => true, 'result' => $this->l('Error in updating customer wallet status.')));
        }
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
        } else {
            die(1);
        }
    }

    public function processAdd()
    {
        $customer = new Customer((int)Tools::getValue('ids_customer'));
        if (Validate::isLoadedObject($customer)) {
            if (!PrepaymentWallets::walletExists($customer->id)) {
                $_POST['id_customer'] = $customer->id;
            } else {
                $this->errors[] = Tools::displayError('A balance is already set for this customer');
            }
        } else {
            $this->errors[] = Tools::displayError('To add a new wallet, you must select a customer');
        }

        return parent::processAdd();
    }
}
