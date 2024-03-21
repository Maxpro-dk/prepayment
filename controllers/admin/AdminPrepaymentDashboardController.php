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

class AdminPrepaymentDashboardController extends ModuleAdminController
{
    public $currency_wallet;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();

        $this->currency_wallet = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_title = $this->l('Dashboard');
        $this->page_header_toolbar_btn['settings'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name,
            'desc' => $this->l('Settings', null, null, false),
            'icon' => 'process-icon-configure'
        );
    }

    public function initToolbar()
    {
        $this->toolbar_btn['edit'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name,
            'desc' => $this->l('Settings')
        );
        $this->toolbar_title = $this->breadcrumbs;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitOpenWallets')) {
            $customers = Customer::getCustomers();

            foreach ($customers as $customer) {
                if (PrepaymentWallets::walletExists((int)$customer['id_customer'])) {
                    continue;
                }

                $wallet = new PrepaymentWallets();
                $wallet->id_customer = (int)$customer['id_customer'];
                $wallet->total_amount = 0;
                $wallet->active = 1;
                $wallet->add();
            }

            Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
        }

        return parent::postProcess();
    }

    public function renderView()
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->smarty->assign(array(
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'title' => $this->toolbar_title,
                'toolbar_scroll' => 'false',
                'token' => $this->token,
            ));

            $this->context->controller->addJS(_MODULE_DIR_.'prepayment/views/js/tools/bootstrap.js');
        }

        $global_data = array();

        $global_data['balance'] = Tools::displayPrice(PrepaymentWallets::getBalance(), $this->currency_wallet);
        $global_data['tendance'] = 0;
        $tendance = PrepaymentLastActivities::getVariation();
        if ($tendance > 0) {
            $global_data['tendance'] = Tools::ps_round((($global_data['balance'] - $tendance) / $tendance) * 100);
        }
        $global_data['movements'] = (int)PrepaymentLastActivities::getMovements();
        $global_data['amount_earned'] = Tools::displayPrice(PrepaymentLastActivities::getAmountEarned());
        $global_data['pending_deposits'] = PrepaymentLastActivities::getPendingMovements(PrepaymentLastActivities::DEPOSIT);
        $global_data['total_deposits'] = PrepaymentLastActivities::getTotalCredits(PrepaymentLastActivities::DEPOSIT);
        $global_data['total_deposits'] += PrepaymentLastActivities::getTotalCredits(PrepaymentLastActivities::CUSTOM_DEPOSIT);
        $global_data['total_orders'] = PrepaymentLastActivities::getTotalCredits(PrepaymentLastActivities::ORDER);
        $global_data['total_refunds'] = PrepaymentLastActivities::getTotalCredits(PrepaymentLastActivities::REFUND);
        $global_data['total_disbursements'] = PrepaymentLastActivities::getTotalCredits(PrepaymentLastActivities::DISBURSEMENT);
        $global_data['total_disbursements'] += PrepaymentLastActivities::getTotalCredits(PrepaymentLastActivities::CUSTOM_DISBURSEMENT);
        $global_data['total_gifts'] = PrepaymentLastActivities::getTotalCredits(PrepaymentLastActivities::GIFT);

        $this->context->smarty->assign(array(
            'global_data' => $global_data,
            'tpl_overview' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/overview.tpl',
            'href_viewsettings' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name,
        ));

        $this->displayWallets();
        $this->displayLastActivities();
        $this->displayPacks();
        $this->displayGifts();

        return $this->context->smarty->createTemplate(_PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/dashboard.tpl', $this->context->smarty)->fetch();
    }

    public function displayWallets()
    {
        $wallets = PrepaymentWallets::getWallets(20);
        foreach ($wallets as &$wallet) {
            $customer = new Customer((int)$wallet['id_customer']);
            $wallet['customer'] = $customer->email;
            $wallet['total_amount'] = Tools::displayPrice($wallet['total_amount'], $this->currency_wallet);
        }

        $stats = PrepaymentWallets::getStats();

        $this->context->smarty->assign(array(
            'href_viewwallets' => $this->context->link->getAdminLink('AdminPrepaymentWallets'),
            'href_addwallet' => $this->context->link->getAdminLink('AdminPrepaymentWallets').'&addprepayment_wallets',
            'wallets' => $wallets,
            'wallet_stats' => $stats,
            'tpl_wallets' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/wallets.tpl',

        ));
    }

    public function displayLastActivities()
    {
        $last_activities = PrepaymentLastActivities::getLastActivities(null, 20);
        foreach ($last_activities as &$last_activity) {
            $customer = new Customer((int)$last_activity['id_customer']);
            $last_activity['customer'] = $customer->email;

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


            if ($last_activity['id_operation'] == PrepaymentLastActivities::CUSTOM_DEPOSIT || $last_activity['id_operation'] == PrepaymentLastActivities::CUSTOM_DISBURSEMENT) {
                $last_activity['href_view'] = $this->context->link->getAdminLink('AdminPrepaymentLastActivities').'&id_prepayment_last_activities='.$last_activity['id_prepayment_last_activities'].'&updateprepayment_last_activities';
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::ORDER && PrepaymentLastActivities::isPartial($last_activity['id_prepayment_last_activities'])) {
                $object = new PrepaymentLastActivities((int)$last_activity['id_prepayment_last_activities']);
                $id_partial = $object->getPartialId();
                $partial = new PrepaymentPartials((int)$id_partial);
                $last_activity['href_view'] = $this->context->link->getAdminLink('AdminCarts').'&id_cart='.$partial->id_cart.'&viewcart';
            } else {
                $last_activity['href_view'] = $this->context->link->getAdminLink('AdminOrders').'&id_order='.$last_activity['id_order'].'&vieworder';
            }

            $last_activity['credits'] = Tools::displayPrice(Tools::convertPriceFull($last_activity['credits'] + $last_activity['extra_credits'], Currency::getCurrencyInstance($last_activity['id_currency']), $this->currency_wallet), $this->currency_wallet->id);
        }

        $stats = PrepaymentLastActivities::getStats();

        $this->context->smarty->assign(array(
            'href_viewlast_activities' => $this->context->link->getAdminLink('AdminPrepaymentLastActivities'),
            'href_addmovement' => $this->context->link->getAdminLink('AdminPrepaymentLastActivities').'&addprepayment_last_activities',
            'last_activities' => $last_activities,
            'last_activity_stats' => $stats,
            'tpl_last_activities' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/last_activities.tpl',
        ));
    }

    public function displayPacks()
    {
        $packs = PrepaymentPacks::getPacks(true, 20);
        foreach ($packs as &$pack) {
            $product = new Product((int)$pack['id_product']);
            $pack['name'] = $product->name[(int)$this->context->language->id];
            $pack['credits'] = Tools::displayPrice($pack['credits'], $this->currency_wallet);
            $pack['extra_credits'] = Tools::displayPrice($pack['extra_credits'], $this->currency_wallet);
        }

        $stats = PrepaymentPacks::getStats();

        $this->context->smarty->assign(array(
            'href_viewpacks' => $this->context->link->getAdminLink('AdminPrepaymentPacks'),
            'href_addpack' => $this->context->link->getAdminLink('AdminPrepaymentPacks').'&addprepayment_packs',
            'packs' => $packs,
            'packs_stats' => $stats,
            'currency' => Currency::getCurrencyInstance(Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS')),
            'tpl_packs' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/packs.tpl',
        ));
    }

    public function displayGifts()
    {
        $gifts = PrepaymentGifts::getGifts(20);
        foreach ($gifts as &$gift) {
            if (isset($gift['gift_percent']) && $gift['gift_percent'] > 0) {
                $gift['reduction'] = $gift['gift_percent'].'%';
            } elseif (isset($gift['gift_amount']) && $gift['gift_amount'] > 0) {
                $gift['reduction'] = Tools::displayPrice($gift['gift_amount'], (int)$gift['gift_currency']);
            } else {
                $gift['reduction'] = 0;
            }
        }

        $stats = PrepaymentGifts::getStats();

        $this->context->smarty->assign(array(
            'href_viewgifts' => $this->context->link->getAdminLink('AdminPrepaymentGifts'),
            'href_addgift' => $this->context->link->getAdminLink('AdminPrepaymentGifts').'&addprepayment_gifts',
            'gifts' => $gifts,
            'gift_stats' => $stats,
            'currency' => Currency::getCurrencyInstance(Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS')),
            'tpl_gifts' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/gifts.tpl',
        ));
    }
}
