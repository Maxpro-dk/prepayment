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

/* Security */
if (!defined('_PS_VERSION_')) {
    exit;
}

/* Checking compatibility with older PrestaShop and fixing it */
if (!defined('_MYSQL_ENGINE_')) {
    define('_MYSQL_ENGINE_', 'MyISAM');
}

require_once(_PS_MODULE_DIR_.'prepayment/models/PrepaymentWallets.php');
require_once(_PS_MODULE_DIR_.'prepayment/models/PrepaymentLastActivities.php');
require_once(_PS_MODULE_DIR_.'prepayment/models/PrepaymentPacks.php');
require_once(_PS_MODULE_DIR_.'prepayment/models/PrepaymentGifts.php');
require_once(_PS_MODULE_DIR_.'prepayment/models/PrepaymentPartials.php');

class Prepayment extends PaymentModule
{
    private $_html = '';

    public $tabs = array(
        0 => array(
            'name' => 'Wallet',
            'className' => 'AdminPrepaymentDashboard',
            'id_parent' => 0
        ),
        1 => array(
            'name' => 'Wallets',
            'className' => 'AdminPrepaymentWallets',
            'id_parent' => -1
        ),
        2 => array(
            'name' => 'Last Activities',
            'className' => 'AdminPrepaymentLastActivities',
            'id_parent' => -1
        ),
        3 => array(
            'name' => 'Packs',
            'className' => 'AdminPrepaymentPacks',
            'id_parent' => -1
        ),
        4 => array(
            'name' => 'Gifts',
            'className' => 'AdminPrepaymentGifts',
            'id_parent' => -1
        )
    );

    public $metas = array(
        0 => array(
            'controller' => 'deposits',
            'title' => array('en' => 'deposits', 'fr' => 'deposits'),
            'description' => array('en' => 'Deposit funds', 'fr' => 'Déposer des fonds'),
        ),
        1 => array(
            'controller' => 'dashboard',
            'title' => array('en' => 'wallet-dashboard', 'fr' => 'wallet-dashboard'),
            'description' => array('en' => 'Wallet details', 'fr' => 'Détails du porte-monnaie'),
        ),
        2 => array(
            'controller' => 'payment'
        ),
        3 => array(
            'controller' => 'validation'
        ),
    );

    public function __construct()
    {
        $this->name                = 'prepayment';
        $this->tab                 = 'payments_gateways';
        $this->version             = '2.4.0';
        $this->author              = 'Keyrnel';
        $this->module_key          = '45433c1bca07dd2a8375cc1d3c081e61';
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName         = $this->l('Wallet');
        $this->description         = $this->l('Prepayment wallet is the simplest payment method which allows customers to deposit funds in their own wallet in order to purchase orders.');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install($delete_params = true)
    {
        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('displayNav')
            || !$this->registerHook('displayNav2')
            || !$this->registerHook('displayTop')
            || !$this->registerHook('leftColumn')
            || !$this->registerHook('adminOrder')
            || !$this->registerHook('displayShoppingCartFooter')
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('actionCustomerAccountAdd')
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('actionProductSave')
            || !$this->registerHook('actionProductDelete')
            || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('actionPaymentCCAdd')
            || !$this->registerHook('actionPaymentConfirmation')
            || !$this->registerHook('displayShoppingCart')
            || !$this->registerHook('payment')
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('displayPaymentTop')
            || !$this->registerHook('cart')
            || !$this->registerHook('displayAdminCustomers')) {
            return false;
        }

        // Install database & conf
        if ($delete_params) {
            if (!$this->installDb() || !$this->installConf()) {
                return false;
            }
        }

        $languages = Language::getLanguages();

        // Install Category
        $category = new Category();
        foreach ($languages as $language) {
            $category->name[(int)$language['id_lang']] = 'Packs';
            $category->link_rewrite[(int)$language['id_lang']] = Tools::link_rewrite($category->name[(int)$language['id_lang']]);
        }
        $category->id_parent = Configuration::get('PS_HOME_CATEGORY');
        $category->is_root_category = 0;
        $category->level_depth = (int)$category->id_parent + 1;
        $category->active = 0;
        if (!$category->add()) {
            return false;
        }

        Configuration::updateValue('WALLET_PACKS_CAT', (int)$category->id);

        // Install Tabs
        foreach ($this->tabs as $tab) {
            $obj = new Tab();
            foreach ($languages as $lang) {
                $obj->name[$lang['id_lang']] = $this->l($tab['name']);
            }
            $obj->class_name = $tab['className'];
            $obj->id_parent = $tab['className'] == 'AdminPrepaymentDashboard' ? (int)Tab::getIdFromClassName('AdminParentCustomer') : $tab['id_parent'];
            $obj->module = $this->name;
            if (!$obj->add()) {
                return false;
            }
        }

        // Install Meta
        foreach ($this->metas as $meta) {
            $obj = new Meta();
            $obj->page = 'module-'.$this->name.'-'.$meta['controller'];
            $obj->configurable = 1;
            foreach ($languages as $language) {
                if ($language['iso_code'] == 'fr') {
                    $obj->title[(int)$language['id_lang']] = isset($meta['title'][$language['iso_code']]) ? $meta['title'][$language['iso_code']] : '';
                    $obj->description[(int)$language['id_lang']] = isset($meta['description'][$language['iso_code']]) ? $meta['description'][$language['iso_code']] : '';
                    $obj->url_rewrite[(int)$language['id_lang']] = Tools::link_rewrite($obj->title[(int)$language['id_lang']]);
                } else {
                    $obj->title[(int)$language['id_lang']] = isset($meta['title'][$language['iso_code']]) ? $meta['title'][$language['iso_code']] : '';
                    $obj->description[(int)$language['id_lang']] = isset($meta['description'][$language['iso_code']]) ? $meta['description'][$language['iso_code']] : '';
                    $obj->url_rewrite[(int)$language['id_lang']] = Tools::link_rewrite($obj->title[(int)$language['id_lang']]);
                }
            }
            if (!$obj->add()) {
                return false;
            }

            if (version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<')) {
                $themes = Theme::getThemes();
                $theme_meta_value = array();
                foreach ($themes as $theme) {
                    $theme_meta_value[] = array(
                        'id_theme' => $theme->id,
                        'id_meta' => (int)$obj->id,
                        'left_column' => (int)$theme->default_left_column,
                        'right_column' => (int)$theme->default_right_column
                    );
                }
                if (count($theme_meta_value) > 0) {
                    Db::getInstance()->insert('theme_meta', (array)$theme_meta_value, false, true, DB::INSERT_IGNORE);
                }
            }
        }

        //init mails
        $this->initMails();

        return true;
    }

    public function initMails()
    {
        $languages = Language::getLanguages();
        $path = _PS_MODULE_DIR_.$this->name.'/mails/en';
        
        foreach ($languages as $language) {
            $dest = _PS_MODULE_DIR_.$this->name.'/mails/'.$language['iso_code'];
            if (file_exists($dest)) {
                continue;
            }

            if (!mkdir($dest, 0777, true)) {
                continue;
            }

            $files = scandir($path);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                copy($path.'/'.$file, $dest.'/'.$file);
            }
        }
        return true;
    }

    protected function installDb()
    {
        $sql = array();
        include(dirname(__FILE__).'/sql/install.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        return true;
    }

    protected function installConf()
    {
        Configuration::updateValue('WALLET_DEFAULT_CURRENCY_PACKS', Configuration::get('PS_CURRENCY_DEFAULT'));
        Configuration::updateValue('WALLET_ALLOW_NEGATIVE_BALANCE', 0);
        Configuration::updateValue('WALLET_NEGATIVE_BALANCE_MAX', 0);
        Configuration::updateValue('WALLET_ALLOW_DISBURSEMENT', 0);
        Configuration::updateValue('WALLET_AUTO_REFUND', 0);
        Configuration::updateValue('WALLET_DISPLAY_PACKS', 1);
        Configuration::updateValue('WALLET_DISPLAY_GIFTS', 1);
        Configuration::updateValue('WALLET_DISPLAY_TOPMENU', 0);
        Configuration::updateValue('WALLET_AUTO_OPEN', 1);
        Configuration::updateValue('WALLET_PARTIAL_PAYMENT', 0);
        Configuration::updateValue('WALLET_NOTIFICATION_DEPOSIT', 0);
        Configuration::updateValue('WALLET_NOTIFICATION_ORDER', 0);
        Configuration::updateValue('WALLET_NOTIFICATION_REFUND', 0);
        Configuration::updateValue('WALLET_NOTIFICATION_DISBURSEMENT', 0);
        Configuration::updateValue('WALLET_NOTIFICATION_GIFT', 0);

        return true;
    }

    public function uninstall($delete_params = true)
    {
        // Uninstall Module
        if (!parent::uninstall()) {
            return false;
        }

        // Delete Prepayment Packs
        $packs = PrepaymentPacks::getPacks();
        foreach ($packs as $pack) {
            $product = new Product($pack['id_product']);
            $product->delete();
        }

        // Uninstall Category
        $category = new Category(Configuration::get('WALLET_PACKS_CAT'));
        $category->delete();

        // Uninstall Meta
        foreach ($this->metas as $meta) {
            $metas = Meta::getMetaByPage('module-'.$this->name.'-'.$meta['controller'], (int)$this->context->language->id);
            $obj = new Meta((int)$metas['id_meta']);
            if ($obj->delete() && version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<')) {
                Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'theme_meta` WHERE id_meta='.(int)$obj->id);
            }
        }

        // Uninstall top-menu link
        $topmenu_class = version_compare(_PS_VERSION_, '1.7', '>=') ? 'Ps_MenuTopLinks' : 'MenuTopLinks';
        if (class_exists($topmenu_class)) {
            $shop_id = (int)Shop::getContextShopID();
            $shop_group_id = Shop::getGroupFromShop($shop_id);
            $conf = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);

            $id_linksmenutop = $this->addTopMenuLink();
            $topmenu_class::remove($id_linksmenutop, $shop_id);

            Configuration::updateValue(
                'MOD_BLOCKTOPMENU_ITEMS',
                (string)str_replace(array('LNK'.$id_linksmenutop.',', 'LNK'.$id_linksmenutop), '', $conf),
                false,
                $shop_group_id,
                $shop_id
            );
        }

        // Uninstall Tabs
        $tabs = Tab::getCollectionFromModule($this->name);
        foreach ($tabs as $tab) {
            $tab->delete();
        }

        // Uninstall database & conf
        if ($delete_params) {
            if (!$this->uninstallDB() || !$this->uninstallConf()) {
                return false;
            }
        }

        return true;
    }

    protected function uninstallDb()
    {
        $sql = array();
        include(dirname(__FILE__).'/sql/uninstall.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        return true;
    }

    protected function uninstallConf()
    {
        Configuration::deleteByName('WALLET_PACKS_CAT');
        Configuration::deleteByName('WALLET_DEFAULT_CURRENCY_PACKS');
        Configuration::deleteByName('WALLET_ALLOW_NEGATIVE_BALANCE');
        Configuration::deleteByName('WALLET_NEGATIVE_BALANCE_MAX');
        Configuration::deleteByName('WALLET_ALLOW_DISBURSEMENT');
        Configuration::deleteByName('WALLET_AUTO_REFUND');
        Configuration::deleteByName('WALLET_DISPLAY_PACKS');
        Configuration::deleteByName('WALLET_DISPLAY_GIFTS');
        Configuration::deleteByName('WALLET_DISPLAY_TOPMENU');
        Configuration::deleteByName('WALLET_AUTO_OPEN');
        Configuration::deleteByName('WALLET_PARTIAL_PAYMENT');
        Configuration::deleteByName('WALLET_NOTIFICATION_GIFT');
        Configuration::deleteByName('WALLET_NOTIFICATION_ORDER');
        Configuration::deleteByName('WALLET_NOTIFICATION_REFUND');
        Configuration::deleteByName('WALLET_NOTIFICATION_DISBURSEMENT');
        Configuration::deleteByName('WALLET_NOTIFICATION_DEPOSIT');

        return true;
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitWalletConfiguration')) {
            foreach ($this->getConfigFieldsValues() as $key => $val) {
                if ($key == 'WALLET_DEFAULT_CURRENCY_PACKS') {
                    $new_wallet_currency = Currency::getCurrencyInstance((int)$val);
                }

                Configuration::updateValue($key, $val);
            }

            if (isset($new_wallet_currency) && !empty($new_wallet_currency)) {
                $this->updateWalletCurrency($new_wallet_currency);
            }

            $topmenu_module = version_compare(_PS_VERSION_, '1.7', '>=') ? 'Ps_MainMenu' : 'blocktopmenu';
            $topmenu = Module::getInstanceByName($topmenu_module);
            if ($topmenu && $topmenu->active) {
                $this->updateTopMenuDisplay();
            }

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&conf=4&token='.Tools::getAdminTokenLite('AdminModules'));
        }

        $this->_html .= $this->renderConfigForm();

        return $this->_html;
    }

    public function renderConfigForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Pack default currency'),
                        'desc' => $this->l('Select the default currency for credit packs.'),
                        'name' => 'WALLET_DEFAULT_CURRENCY_PACKS',
                        'options' => array(
                            'query' => Currency::getCurrencies(),
                            'id' => 'id_currency',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Enable partial payment'),
                        'desc' => $this->l('Allow customers to pay orders both with wallet credits left & other payment methods.'),
                        'name' => 'WALLET_PARTIAL_PAYMENT',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Enable negative balance'),
                        'desc' => $this->l('Allow customers to pay orders with negative balance.'),
                        'name' => 'WALLET_ALLOW_NEGATIVE_BALANCE',
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
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Maximum negative balance to purchase orders'),
                        'desc' => $this->l('Set to 0 to disable this feature.'),
                        'name' => 'WALLET_NEGATIVE_BALANCE_MAX',
                        'col' => '2',
                        'suffix' => Currency::getCurrencyInstance(Configuration::get('PS_CURRENCY_DEFAULT'))->sign,
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Open wallet with customer account add'),
                        'desc' => $this->l('Enable this option to automatically open customer wallet on account creation.'),
                        'name' => 'WALLET_AUTO_OPEN',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Disbursement on deposits'),
                        'desc' => $this->l('Enable this option to allow refund of deposits. The deposit amount will be directly debited from the customer wallet.'),
                        'name' => 'WALLET_ALLOW_DISBURSEMENT',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Refund orders in wallet'),
                        'desc' => $this->l('Enable this option to automatically credit back customer wallet on refund of orders, even if the order has been paid with another payment method'),
                        'name' => 'WALLET_AUTO_REFUND',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Display packs in column'),
                        'desc' => $this->l('Enable this option to display active packs in the left/right column.'),
                        'name' => 'WALLET_DISPLAY_PACKS',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Display gift rules in column'),
                        'desc' => $this->l('Enable this option to display active gift rules in the left/right column.'),
                        'name' => 'WALLET_DISPLAY_GIFTS',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Display a deposit funds link in top menu'),
                        'desc' => $this->l('Enable this option to add a quick link to the deposit funds page in the top menu.'),
                        'name' => 'WALLET_DISPLAY_TOPMENU',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Send email on deposit process'),
                        'desc' => $this->l('Enable this option to send an email informing about user wallet balance on deposit process.'),
                        'name' => 'WALLET_NOTIFICATION_DEPOSIT',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Send email on order process'),
                        'desc' => $this->l('Enable this option to send an email informing about user wallet balance on order process.'),
                        'name' => 'WALLET_NOTIFICATION_ORDER',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Send email on cash back process'),
                        'desc' => $this->l('Enable this option to send an email informing about user wallet balance on cash back process.'),
                        'name' => 'WALLET_NOTIFICATION_GIFT',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Send email on refund process'),
                        'desc' => $this->l('Enable this option to send an email informing about user wallet balance on refund process.'),
                        'name' => 'WALLET_NOTIFICATION_REFUND',
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
                        )
                    ),
                    array(
                        'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'label' => $this->l('Send email on disbursement process'),
                        'desc' => $this->l('Enable this option to send an email informing about user wallet balance on disbursement process.'),
                        'name' => 'WALLET_NOTIFICATION_DISBURSEMENT',
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
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => version_compare(_PS_VERSION_, '1.6', '<') ? 'button' : 'btn btn-default pull-right',
                    'name' => 'submitModerate',
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->name;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWalletConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'WALLET_DEFAULT_CURRENCY_PACKS' => (int)Tools::getValue('WALLET_DEFAULT_CURRENCY_PACKS', Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS')),
            'WALLET_ALLOW_NEGATIVE_BALANCE' => (bool)Tools::getValue('WALLET_ALLOW_NEGATIVE_BALANCE', Configuration::get('WALLET_ALLOW_NEGATIVE_BALANCE')),
            'WALLET_NEGATIVE_BALANCE_MAX' => (int)Tools::getValue('WALLET_NEGATIVE_BALANCE_MAX', Configuration::get('WALLET_NEGATIVE_BALANCE_MAX')),
            'WALLET_ALLOW_DISBURSEMENT' => (bool)Tools::getValue('WALLET_ALLOW_DISBURSEMENT', Configuration::get('WALLET_ALLOW_DISBURSEMENT')),
            'WALLET_AUTO_REFUND' => (bool)Tools::getValue('WALLET_AUTO_REFUND', Configuration::get('WALLET_AUTO_REFUND')),
            'WALLET_DISPLAY_PACKS' => (bool)Tools::getValue('WALLET_DISPLAY_PACKS', Configuration::get('WALLET_DISPLAY_PACKS')),
            'WALLET_DISPLAY_GIFTS' => (bool)Tools::getValue('WALLET_DISPLAY_GIFTS', Configuration::get('WALLET_DISPLAY_GIFTS')),
            'WALLET_AUTO_OPEN' => (bool)Tools::getValue('WALLET_AUTO_OPEN', Configuration::get('WALLET_AUTO_OPEN')),
            'WALLET_DISPLAY_TOPMENU' => (bool)Tools::getValue('WALLET_DISPLAY_TOPMENU', Configuration::get('WALLET_DISPLAY_TOPMENU')),
            'WALLET_PARTIAL_PAYMENT' => (bool)Tools::getValue('WALLET_PARTIAL_PAYMENT', Configuration::get('WALLET_PARTIAL_PAYMENT')),
            'WALLET_NOTIFICATION_DEPOSIT' => (bool)Tools::getValue('WALLET_NOTIFICATION_DEPOSIT', Configuration::get('WALLET_NOTIFICATION_DEPOSIT')),
            'WALLET_NOTIFICATION_ORDER' => (bool)Tools::getValue('WALLET_NOTIFICATION_ORDER', Configuration::get('WALLET_NOTIFICATION_ORDER')),
            'WALLET_NOTIFICATION_REFUND' => (bool)Tools::getValue('WALLET_NOTIFICATION_REFUND', Configuration::get('WALLET_NOTIFICATION_REFUND')),
            'WALLET_NOTIFICATION_DISBURSEMENT' => (bool)Tools::getValue('WALLET_NOTIFICATION_DISBURSEMENT', Configuration::get('WALLET_NOTIFICATION_DISBURSEMENT')),
            'WALLET_NOTIFICATION_GIFT' => (bool)Tools::getValue('WALLET_NOTIFICATION_GIFT', Configuration::get('WALLET_NOTIFICATION_GIFT'))
        );
    }

    protected function updateWalletCurrency($currency)
    {
        $default_currency = Currency::getCurrencyInstance((int)Configuration::get('PS_CURRENCY_DEFAULT'));

        //update deposits
        $packs = PrepaymentPacks::getPacks();
        foreach ($packs as $pack) {
            $product = new Product((int)$pack['id_product']);
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $product->price = Tools::convertPriceFull($pack['credits'], $currency, $default_currency);
            $product->update();
        }

        //update Wallets
        $wallets = PrepaymentWallets::getWallets();
        foreach ($wallets as $wallet) {
            $wallet_object = new PrepaymentWallets((int)$wallet['id_prepayment_wallets']);
            PrepaymentLastActivities::updateWallet($wallet_object);
        }

        return true;
    }

    protected function addTopMenuLink()
    {
        $id_linksmenutop = 0;
        $topmenu_class = version_compare(_PS_VERSION_, '1.7', '>=') ? 'Ps_MenuTopLinks' : 'MenuTopLinks';
        $labels = array();
        $page_link = array();
        $languages = Language::getLanguages();

        foreach ($languages as $language) {
            $label = ($language['iso_code'] == 'fr') ? 'Déposer des fonds' : 'Deposit money';
            $labels[(int)$language['id_lang']] = $label;
            $page_link[(int)$language['id_lang']] = $this->context->link->getModuleLink($this->name, 'deposits', array(), null, (int)$language['id_lang']);
            $links = $topmenu_class::gets((int)$language['id_lang'], null, (int)Shop::getContextShopID());
            foreach ($links as $link) {
                if ($link['link'] == $page_link[(int)$language['id_lang']]) {
                    $id_linksmenutop = (int)$link['id_linksmenutop'];
                    break 2;
                }
            }
        }
        if ($id_linksmenutop == 0) {
            $topmenu_class::add($page_link, $labels, 0, (int)Shop::getContextShopID());
            $id_linksmenutop = $this->addTopMenuLink();
        }

        return $id_linksmenutop;
    }

    protected function updateTopMenuDisplay()
    {
        $shop_id = (int)Shop::getContextShopID();
        $shop_group_id = Shop::getGroupFromShop($shop_id);
        $conf = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);

        $display = Configuration::get('WALLET_DISPLAY_TOPMENU');

        $id_linksmenutop = $this->addTopMenuLink();

        if (!$display) {
            $topmenu_class = version_compare(_PS_VERSION_, '1.7', '>=') ? 'Ps_MenuTopLinks' : 'MenuTopLinks';
            $topmenu_class::remove($id_linksmenutop, $shop_id);

            Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', (string)str_replace(array('LNK'.$id_linksmenutop.',', 'LNK'.$id_linksmenutop), '', $conf), false, $shop_group_id, $shop_id);
        } else {
            $menu_items = Tools::strlen($conf) ? explode(',', $conf) : array();
            if (!in_array('LNK'.$id_linksmenutop, $menu_items)) {
                $menu_items[] = 'LNK'.$id_linksmenutop;
            }

            Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', (string)implode(',', $menu_items), false, (int)$shop_group_id, (int)$shop_id);
        }

        //clear top menu cache
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $dir = _PS_CACHE_DIR_.DIRECTORY_SEPARATOR.'ps_mainmenu';
            if (is_dir($dir)) {
                foreach (scandir($dir) as $entry) {
                    if (preg_match('/\.json$/', $entry)) {
                        unlink($dir.DIRECTORY_SEPARATOR.$entry);
                    }
                }
            }
        } else {
            $this->_clearCache('blocktopmenu.tpl');
        }

        return true;
    }

    private function _makeRefund($order, $id_order_slip, $id_wallet)
    {
        $last_activity = new PrepaymentLastActivities();
        $last_activity->id_order = (int)$order->id;
        $last_activity->id_wallet = (int)$id_wallet;
        $last_activity->id_currency = (int)$order->id_currency;
        $last_activity->id_customer = (int)$order->id_customer;
        $last_activity->reference = $order->reference;
        $last_activity->extra_credits = 0;
        $last_activity->paid = 1;


        $order_slip = new OrderSlip((int)$id_order_slip);
        $slip_amount = $order_slip->total_shipping_tax_incl;
        $message = '';

        $products_ret = OrderSlip::getOrdersSlipDetail((int)$id_order_slip);
        foreach ($products_ret as $slip_detail) {
            $slip_amount += $slip_detail['amount_tax_incl'];
        }

        // check if at least one deposit is linked to the order
        $ids_operation = PrepaymentLastActivities::getOperationsByIdOrder((int)$order->id);
        $match = false;
        foreach ($ids_operation as $id_operation) {
            if ($id_operation == PrepaymentLastActivities::DEPOSIT) {
                $match = true;
                break;
            }
        }

        if ($match) {
            if (!Configuration::get('WALLET_ALLOW_DISBURSEMENT')) {
                return;
            }

            $last_activity->id_operation = PrepaymentLastActivities::DISBURSEMENT;
            $message .= $this->l('Disbursement into wallet: ').$slip_amount;
        } else {
            if ($order->module != $this->name && !Configuration::get('WALLET_AUTO_REFUND')) {
                return;
            }

            $last_activity->id_operation = PrepaymentLastActivities::REFUND;
            $message .= $this->l('Refund into wallet: ').$slip_amount;
        }


        $last_activity->credits = $slip_amount;
        if ($last_activity->add()) {
            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'prepayment_refunds` (`id_prepayment_last_activities`, `id_order_slip`) VALUES ('.(int)$last_activity->id.', '.(int)$id_order_slip.')');
            $this->_addNewPrivateMessage((int)$order->id, $message);
        }
    }

    public function _addNewPrivateMessage($id_order, $message)
    {
        $new_message = new Message();
        $message = strip_tags($message, '<br>');

        if (!Validate::isCleanHtml($message)) {
            $message = $this->l('Payment message is not valid, please check your module.');
        }

        $new_message->message = $message;
        $new_message->id_order = (int)$id_order;
        $new_message->private = 1;

        return $new_message->add();
    }

    public function checkCurrency($cart)
    {
        $currency_order = Currency::getCurrencyInstance((int)$cart->id_currency);
        $currencies_module = $this->getCurrency((int)$cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        $gift = PrepaymentGifts::getGift(null, $this->context, false);
        if (isset($gift) && !empty($gift)) {
            $gift_amount = Tools::displayPrice($gift->getContextualValue(true), Context::getContext()->currency->id);
            $this->context->smarty->assign('gift_amount', $gift_amount);
            return $this->display(__FILE__, 'shopping-cart.tpl');
        }
        return false;
    }

    public function hookRightColumn($params)
    {
        $packs = array();
        $gifts = array();

        if (Configuration::get('WALLET_DISPLAY_PACKS')) {
            $packs = PrepaymentPacks::getPacks(true);
        }

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Context::getContext()->currency;
        foreach ($packs as &$pack) {
            $pack['credits'] = Tools::displayPrice(Tools::convertPriceFull($pack['credits'], $from_currency, $to_currency));
            $extra_credits = Tools::convertPriceFull($pack['extra_credits'], $from_currency, $to_currency);
            $pack['extra_credits'] = $extra_credits > 0 ? Tools::displayPrice($extra_credits) : null;
        }

        if (Configuration::get('WALLET_DISPLAY_GIFTS')) {
            $gifts = PrepaymentGifts::getCustomerGifts((int)$this->context->language->id, (int)$this->context->customer->id, true, true, true);
        }

        foreach ($gifts as $k => $gift) {
            $current_object = new PrepaymentGifts($gift['id_prepayment_gifts']);
            $gifts[$k]['countries'] = $current_object->getAssociatedRestrictions('country', true, true);
            $gifts[$k]['groups'] = $current_object->getAssociatedRestrictions('group', false, true);
            $gifts[$k]['shops'] = $current_object->getAssociatedRestrictions('shop', false, false);
            $gifts[$k]['payments'] = $current_object->getAssociatedRestrictions('payment', false, false);
            $gifts[$k]['carriers'] = $current_object->getAssociatedRestrictions('carrier', true, false);
            $gifts[$k]['product_rule_groups'] = $current_object->getProductRuleGroups();

            if (isset($gift['minimum_amount']) && $gift['minimum_amount'] > 0) {
                $gifts[$k]['minimum_amount'] = Tools::displayPrice(Tools::convertPriceFull($gift['minimum_amount'], Currency::getCurrencyInstance((int)$gift['minimum_amount_currency']), $to_currency));
            } else {
                $gift['minimum_amount'] = null;
            }

            if (isset($gift['gift_amount']) && $gift['gift_amount'] > 0) {
                $gifts[$k]['gift_amount'] = Tools::displayPrice(Tools::convertPriceFull($gift['gift_amount'], Currency::getCurrencyInstance((int)$gift['gift_currency']), $to_currency));
            } else {
                $gift['gift_amount'] = null;
            }

            foreach ($gifts[$k]['product_rule_groups'] as &$product_rule_group) {
                foreach ($product_rule_group['product_rules'] as &$product_rule) {
                    foreach ($product_rule['values'] as $key => $item) {
                        if ($product_rule['type'] == 'products') {
                            $product_rule['values'][$key] = Product::getProductName((int)$item);
                        } elseif ($product_rule['type'] == 'categories') {
                            $category = new Category((int)$item);
                            $product_rule['values'][$key] = $category->getName();
                        } elseif ($product_rule['type'] == 'manufacturers') {
                            $product_rule['values'][$key] = Manufacturer::getNameById((int)$item);
                        } elseif ($product_rule['type'] == 'suppliers') {
                            $product_rule['values'][$key] = Supplier::getNameById((int)$item);
                        } elseif ($product_rule['type'] == 'attributes') {
                            $attribute = new Attribute((int)$item);
                            $attribute_group = new AttributeGroup((int)$attribute->id_attribute_group);
                            $product_rule['values'][$key] = $attribute_group->name[$this->context->language->id].' : '.$attribute->name[$this->context->language->id];
                        }
                    }
                }
            }
        }

        $this->smarty->assign(array(
            'packs' => count($packs) > 0 ? $packs : null,
            'gifts' => count($gifts) > 0 ? $gifts : null
        ));

        return $this->display(__FILE__, 'column.tpl');
    }

    public function hookLeftColumn($params)
    {
        return $this->hookRightColumn($params);
    }

    public function hookDisplayNav($params)
    {
        $id_customer = (int)$this->context->customer->id;
        if (!($wallet = PrepaymentWallets::getWalletInstance($id_customer))) {
            return;
        }

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Context::getContext()->currency;
        $balance = Tools::displayPrice(Tools::convertPriceFull($wallet->total_amount, $from_currency, $to_currency), Context::getContext()->currency->id);

        $this->smarty->assign(array(
            'is_logged' => $this->context->customer->isLogged(),
            'balance' => $balance
        ));

        return $this->display(__FILE__, 'nav.tpl');
    }

    public function hookDisplayNav2($params)
    {
        return $this->hookDisplayNav($params);
    }

    public function hookDisplayTop($params)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            return $this->hookDisplayNav($params);
        }

        return;
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCSS($this->_path.'views/css/admin-design.css', 'all');
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addCss($this->_path.'views/css/admin-theme.css', 'all');
            $this->context->controller->addCSS($this->_path.'views/css/font-awesome.css', 'all');
        }
    }

    public function hookHeader($params)
    {       
        $this->context->controller->addCSS($this->_path.'views/css/design.css', 'all');
        //$this->context->controller->addCSS($this->_path.'views/css/font-awesome.css', 'all');
        $this->context->controller->addjqueryPlugin('fancybox');
     
         if (method_exists($this->context->controller, 'getProduct') && ($product = $this->context->controller->getProduct())) {
            if (PrepaymentPacks::packExists((int)$product->id)) {
                Tools::redirect($this->context->link->getModuleLink($this->name, 'deposits'));
            }
        }

        if (method_exists($this->context->controller, 'getCategory') && ($category = $this->context->controller->getCategory())) {
            if ($category->id == Configuration::get('WALLET_PACKS_CAT')) {
                Tools::redirect($this->context->link->getModuleLink($this->name, 'deposits'));
            }
        }
            
        $callable_controllers = array('cart', 'order-opc', 'order');
        if (isset($this->context->controller->php_self)
            && in_array($this->context->controller->php_self, $callable_controllers)
            && ($cart = $this->context->cart)
            && ($partial = PrepaymentPartials::getPartialInstance((int)$cart->id))
            && !$cart->getDiscountsCustomer((int)$partial->id_cart_rule)) {
            $last_activity = new PrepaymentLastActivities((int)$partial->id_prepayment_last_activities);
            if (Validate::isLoadedObject($last_activity)) {
                $last_activity->delete();
            }

            $cart->removeCartRule((int)$partial->id_cart_rule);

            $cart_rule = new CartRule((int)$partial->id_cart_rule);
            if (Validate::isLoadedObject($cart_rule)) {
                $cart_rule->delete();
            }

            $partial->delete();
        }

        return;
    }

    public function hookAdminOrder($params)
    {
        $order = new Order((int)$params['id_order']);
        if (!$this->active || !($wallet = PrepaymentWallets::getWalletInstance((int)$order->id_customer))) {
            return;
        }

        $order_slips = OrderSlip::getOrdersSlip((int)$order->id_customer, (int)$order->id);
        foreach ($order_slips as $order_slip) {
            $match = false;
            if (PrepaymentLastActivities::refundExists((int)$order_slip['id_order_slip'])) {
                $match = true;
            }
            if ($order_slip['partial'] == 1) {
                $cart_rules = CartRule::getCustomerCartRules((int)$this->context->language->id, (int)$order->id_customer, true, false);
                foreach ($cart_rules as $cart_rule) {
                    if ($cart_rule['code'] == sprintf('V%1$dC%2$dO%3$d', $cart_rule['id_cart_rule'], $order->id_customer, $order->id)) {
                        $match = true;
                    }
                }
            }
            if (!$match) {
                $this->_makeRefund($order, (int)$order_slip['id_order_slip'], (int)$wallet->id);
            }
        }
    }

    public function hookDisplayAdminCustomers($params)
    {
        $id_customer = (int)$params['id_customer'];
        if (!($wallet = PrepaymentWallets::getWalletInstance($id_customer))) {
            return;
        }

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Context::getContext()->currency;

        $balance = $wallet ? Tools::displayPrice(Tools::convertPriceFull($wallet->total_amount, $from_currency, $to_currency), $to_currency) : false;
        $is_active = $wallet ? $wallet->active : false;

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

            $last_activity['display_price'] = Tools::displayPrice($last_activity['price'], Currency::getCurrencyInstance($last_activity['id_currency']));

            $total_credits = Tools::convertPriceFull($last_activity['credits'] + $last_activity['extra_credits'], Currency::getCurrencyInstance($last_activity['id_currency']), $to_currency);
            $last_activity['display_credits'] = Tools::displayPrice($total_credits, $to_currency);
            $last_activity['is_partial'] = PrepaymentLastActivities::isPartial($last_activity['id_prepayment_last_activities']) ? true : false;
        }

        $this->context->smarty->assign(array(
            'wallet' => $wallet,
            'balance' => $balance,
            'history_list' => $last_activities
        ));

        return $this->display(__FILE__, 'admin_customers.tpl');
    }

    public function hookActionCustomerAccountAdd($params)
    {
        if (!Configuration::get('WALLET_AUTO_OPEN')) {
            return false;
        }
        if ($wallet = PrepaymentWallets::walletExists((int)$params['newCustomer']->id)) {
            return false;
        }

        $wallet = new PrepaymentWallets();
        $wallet->id_customer = (int)$params['newCustomer']->id;
        $wallet->total_amount = 0;
        $wallet->active = 1;
        $wallet->add();

        return true;
    }

    public function hookDisplayCustomerAccount($params)
    {
        return $this->display(__FILE__, 'customer_account.tpl');
    }

    public function hookActionProductDelete($params)
    {
        if (!($pack = PrepaymentPacks::getPackInstance((int)$params['product']->id))) {
            return false;
        }

        $pack->delete();
        return true;
    }

    public function hookActionProductSave($params)
    {
        $product = new Product((int)$params['id_product']);
        if (!Validate::isLoadedObject($product) || $product->id_category_default != Configuration::get('WALLET_PACKS_CAT')) {
            return false;
        }

        if (!($pack = PrepaymentPacks::getPackInstance((int)$product->id))) {
            $pack = new PrepaymentPacks();
        }

        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $pack->name[(int)$language['id_lang']] = $product->name[$language['id_lang']];
        }

        $pack->id_product = $product->id;
        $pack->name = $product->name;
        $pack->id_currency = (int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS');
        $pack->credits = Tools::getValue('credits') ? Tools::getValue('credits') : $pack->credits;
        $pack->extra_credits = Tools::getValue('extra_credits') ? Tools::getValue('extra_credits') : $pack->extra_credits;
        $pack->active = $product->active;
        $pack->save();

        return true;
    }

    public function hookPaymentReturn($params)
    {
        $order = version_compare(_PS_VERSION_, '1.7', '<') ? $params['objOrder'] : $params['order'];

        if (!$this->active || !(PrepaymentWallets::walletExists((int)$order->id_customer))) {
            return;
        }

        $state = $order->getCurrentState();
        if ($state == Configuration::get('PS_OS_PAYMENT') || $state == Configuration::get('PS_OS_OUTOFSTOCK')) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($order->getOrdersTotalPaid(), new Currency($order->id_currency), false),
                'shop_name' => $this->context->shop->name,
                'status' => 'ok',
                'id_order' => (int)$order->id
            ));
            if (isset($order->reference) && !empty($order->reference)) {
                $this->smarty->assign('reference', $order->reference);
            }
        } else {
            $this->smarty->assign('status', 'failed');
        }
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookPaymentOptions($params)
    {
        $cart = $params['cart'];
        $wallet = PrepaymentWallets::getWalletInstance((int)$cart->id_customer);

        if (!$this->active
            || !$this->checkCurrency($cart)
            || PrepaymentPacks::countPack($cart->getProducts())
            || !($wallet && $wallet->active)
            || PrepaymentPartials::partialExists((int)$cart->id)) {
            return;
        }

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Currency::getCurrencyInstance((int)$cart->id_currency);

        $order = $cart->getOrderTotal(true, Cart::BOTH);
        $balance = Tools::convertPriceFull($wallet->total_amount, $from_currency, $to_currency);
        $balance_net = Tools::convertPriceFull($wallet->total_amount + Configuration::get('WALLET_NEGATIVE_BALANCE_MAX'), $from_currency, $to_currency);
        if ($balance_net <= 0) {
            return;
        }

        $to_be_paid = 0;
        $is_partial = false;

        if (Configuration::get('WALLET_PARTIAL_PAYMENT') && ($order > $balance_net) && ($balance_net > 0)) {
            $total_products = $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
            $debited_amount = $total_products < $balance_net ? $total_products : $balance_net;
            $to_be_paid = $order - $debited_amount;
            $is_partial = true;

            $order = $debited_amount;
        }

        $current_balance = Tools::displayPrice($balance);
        $total_order = Tools::displayPrice($order);
        $total_balance = Tools::displayPrice($balance - $order);
        $total_to_be_paid = Tools::displayPrice($to_be_paid);

        $this->smarty->assign(array(
            'current_balance' => $current_balance,
            'total_order' => $total_order,
            'total_balance' => $total_balance,
            'total_to_be_paid' => $total_to_be_paid,
            'is_partial' => $is_partial
        ));

        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setCallToActionText($this->l('Pay with my wallet'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ->setAdditionalInformation($this->fetch('module:prepayment/views/templates/front/payment_infos.tpl'));

        return [$newOption];
    }

    public function hookPayment($params)
    {
        $wallet = PrepaymentWallets::getWalletInstance((int)$params['cart']->id_customer);

        if (!$this->active
            || !$this->checkCurrency($params['cart'])
            || PrepaymentPacks::countPack($params['cart']->getProducts())
            || !($wallet && $wallet->active)
            || PrepaymentPartials::partialExists((int)$params['cart']->id)) {
            return;
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_wallet' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookActionPaymentConfirmation($params)
    {
        if (!$this->active
            || !($order = new Order($params['id_order']))
            || !Validate::isLoadedObject($order)
            || !PrepaymentWallets::walletExists((int)$order->id_customer)) {
            return false;
        }

        $ids_last_activity = PrepaymentLastActivities::getIdsByIdOrder((int)$order->id);
        foreach ($ids_last_activity as $id_last_activity) {
            $last_activity = new PrepaymentLastActivities((int)$id_last_activity['id_prepayment_last_activities']);
            if (!Validate::isLoadedObject($last_activity)) {
                continue;
            }

            $last_activity->paid = 1;
            $last_activity->update();
        }

        return true;
    }

    public function hookActionValidateOrder($params)
    {
        if (!$this->active || !($order = $params['order'])) {
            return false;
        }

        if (!($wallet = PrepaymentWallets::getWalletInstance((int)$order->id_customer))) {
            $wallet = new PrepaymentWallets();
            $wallet->id_customer = (int)$order->id_customer;
            $wallet->active = 1;
            $wallet->add();
        }

        $gift = PrepaymentGifts::getGift($order, $this->context);
        if (is_object($gift)) {
            $last_activity = new PrepaymentLastActivities();
            $last_activity->id_order = (int)$order->id;
            $last_activity->id_wallet = (int)$wallet->id;
            $last_activity->id_currency = (int)$order->id_currency;
            $last_activity->id_customer = (int)$order->id_customer;
            $last_activity->reference = $gift->reference;
            $last_activity->price = 0;
            $last_activity->credits = $gift->getContextualValue(true, $this->context);
            $last_activity->extra_credits = 0;
            $last_activity->id_operation = PrepaymentLastActivities::GIFT;
            $last_activity->paid = $order->module == 'prepayment' ? 1 : 0;
            $last_activity->add();

            $gift = new PrepaymentGifts((int)$gift->id);
            $gift->quantity = $gift->quantity - 1;
            $gift->update();
        }

        $products = $order->getProductsDetail();
        if (PrepaymentPacks::countPack($products)) {
            $summary = PrepaymentLastActivities::getSummary($order);
            $last_activity = new PrepaymentLastActivities();
            $last_activity->id_order = (int)$order->id;
            $last_activity->id_wallet = (int)$wallet->id;
            $last_activity->id_currency = (int)$order->id_currency;
            $last_activity->id_customer = (int)$order->id_customer;
            $last_activity->reference = $order->reference;
            $last_activity->price = $summary['price'];
            $last_activity->credits = $summary['credits'];
            $last_activity->extra_credits = $summary['extra_credits'];
            $last_activity->id_operation = PrepaymentLastActivities::DEPOSIT;
            $last_activity->paid = 0;
            $last_activity->add();
        } elseif ($partial = PrepaymentPartials::getPartialInstance((int)$order->id_cart)) {
            $cart_rule = new CartRule((int)$partial->id_cart_rule);
            if (Validate::isLoadedObject($cart_rule)) {
                $cart_rule->delete();
            }

            $last_activity = new PrepaymentLastActivities((int)$partial->id_prepayment_last_activities);
            if (Validate::isLoadedObject($last_activity)) {
                $last_activity->id_order = (int)$order->id;
                $last_activity->reference = $order->reference;
                $last_activity->update();
            }
        }

        return true;
    }

    public function hookActionPaymentCCAdd($params)
    {
        if (!isset($params['paymentCC'])
            || empty($params['paymentCC'])) {
            return false;
        }

        $id_order = false;
        $reference = ltrim($params['paymentCC']->order_reference, '#');
        $orders = Order::getByReference($reference);
        if ($orders) {
            foreach ($orders as $order) {
                $id_order = $order->id;
                break;
            }
        }

        if (!$id_order
            || !($order = new Order((int)$id_order))
            || !Validate::isLoadedObject($order)
            || !($wallet = PrepaymentWallets::getWalletInstance($order->id_customer))
            || $params['paymentCC']->payment_method != $this->displayName
            || PrepaymentPartials::getPartialInstance((int)$order->id_cart)) {
            return false;
        }

        $summary = PrepaymentLastActivities::getSummary($order, $params['paymentCC']->amount, true);
        $last_activity = new PrepaymentLastActivities();
        $last_activity->id_order = (int)$order->id;
        $last_activity->id_wallet = (int)$wallet->id;
        $last_activity->id_currency = $params['paymentCC']->id_currency;
        $last_activity->id_customer = (int)$order->id_customer;
        $last_activity->reference = $reference;
        $last_activity->price = (float)$summary['price'];
        $last_activity->credits = $summary['credits'];
        $last_activity->extra_credits = $summary['extra_credits'];
        $last_activity->id_operation = PrepaymentLastActivities::ORDER;
        $last_activity->paid = 1;
        $last_activity->add();

        return true;
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if (!$this->active
            || !($order = new Order($params['id_order']))
            || !Validate::isLoadedObject($order)
            || !PrepaymentWallets::walletExists((int)$order->id_customer)) {
            return false;
        }

        $order_status = $params['newOrderStatus'];
        if ($order_status->paid == 1
            && ($partial = PrepaymentPartials::getPartialInstance((int)$order->id_cart))
            && $partial->active) {
            $order_cart_rules = $order->getCartRules();
            foreach ($order_cart_rules as $order_cart_rule) {
                if ($order_cart_rule['id_cart_rule'] !== $partial->id_cart_rule) {
                    continue;
                }

                $order_cart_rule = new OrderCartRule((int)$order_cart_rule['id_order_cart_rule']);
                if (Validate::isLoadedObject($order_cart_rule) && $order_cart_rule->id_order == $order->id) {
                    //Update amounts of order
                    $order->total_discounts -= $order_cart_rule->value;
                    $order->total_discounts_tax_incl -= $order_cart_rule->value;
                    $order->total_discounts_tax_excl -= $order_cart_rule->value_tax_excl;

                    $order->total_paid += $order_cart_rule->value;
                    $order->total_paid_tax_incl += $order_cart_rule->value;
                    $order->total_paid_tax_excl += $order_cart_rule->value_tax_excl;

                    // Delete Order Cart Rule and update Order
                    $order_cart_rule->delete();
                }
            }

            $order->update();

            $last_activity = new PrepaymentLastActivities((int)$partial->id_prepayment_last_activities);
            if (!Validate::isLoadedObject($last_activity)) {
                return false;
            }

            $order_invoice = null;
            if ($order->hasInvoice()) {
                $order_invoice = new OrderInvoice((int)$order->invoice_number);
            }

            $order->addOrderPayment($last_activity->credits, $this->displayName, null, Currency::getCurrencyInstance((int)$last_activity->id_currency), null, $order_invoice);
            PrepaymentPartials::deleteDiscountOnInvoice($order_invoice, $order_cart_rule->value, $order_cart_rule->value_tax_excl);

            // Disabled partial
            $partial->active = false;
            $partial->update();
        }

        return true;
    }

    public function hookDisplayPaymentTop()
    {
        $cart = $this->context->cart;

        if (($partial = PrepaymentPartials::getPartialInstance((int)$cart->id))
            && $cart->getDiscountsCustomer((int)$partial->id_cart_rule)) {
            $last_activity = new PrepaymentLastActivities((int)$partial->id_prepayment_last_activities);
            if (Validate::isLoadedObject($last_activity)) {
                $total_paid = Tools::displayPrice((float)$last_activity->credits, (int)$last_activity->id_currency, false);
                $this->smarty->assign(array(
                    'total_paid' => $total_paid
                ));
                return $this->display(__FILE__, 'payment-top.tpl');
            }
        }

        return;
    }

    public function hookCart($params)
    {
        if (!($cart = $this->context->cart)) {
            return false;
        }

        if (($partial = PrepaymentPartials::getPartialInstance((int)$cart->id))
            && $cart->getDiscountsCustomer((int)$partial->id_cart_rule)) {
            $last_activity = new PrepaymentLastActivities((int)$partial->id_prepayment_last_activities);
            if (!Validate::isLoadedObject($last_activity)) {
                return false;
            }

            $cart_rule = new CartRule((int)$partial->id_cart_rule);
            if (!Validate::isLoadedObject($cart_rule)) {
                return false;
            }

            $cart_rule_contextual = $this->getCartRuleContextualValue($cart_rule);
            $last_activity->price = (float)$cart->getOrderTotal(true, Cart::BOTH) + $cart_rule_contextual;
            $last_activity->id_currency = $cart->id_currency;
            $last_activity->update();

            if ($cart_rule_contextual != $cart_rule->reduction_amount) {
                $last_activity->delete();
                $cart->removeCartRule((int)$partial->id_cart_rule);
                $cart_rule->delete();
                $partial->delete();
            }
        }
        return true;
    }

    public function getCartRuleContextualValue($cart_rule)
    {
        $use_tax = true;
        $context = Context::getContext();
        $filter = CartRule::FILTER_ACTION_ALL;
        $all_products = $context->cart->getProducts();

        $cache_id = 'getContextualValue_'.(int)$cart_rule->id.'_'.(int)$use_tax.'_'.(int)$context->cart->id.'_'.(int)$filter;
        foreach ($all_products as $product) {
            $cache_id .= '_'.(int)$product['id_product'].'_'.(int)$product['id_product_attribute'].(isset($product['in_stock']) ? '_'.(int)$product['in_stock'] : '');
        }

        if (Cache::isStored($cache_id)) {
            Cache::clean($cache_id);
        }

        return $cart_rule->getContextualValue($use_tax, $context, $filter);
    }

    public function processNotification($id_customer, $id_operation, $old_wallet_balance, $new_wallet_balance, $id_currency)
    {
        if ((($id_operation == PrepaymentLastActivities::DEPOSIT || $id_operation == PrepaymentLastActivities::CUSTOM_DEPOSIT) && !Configuration::get('WALLET_NOTIFICATION_DEPOSIT'))
            || (($id_operation == PrepaymentLastActivities::DISBURSEMENT || $id_operation == PrepaymentLastActivities::CUSTOM_DISBURSEMENT) && !Configuration::get('WALLET_NOTIFICATION_DISBURSEMENT'))
            || ($id_operation == PrepaymentLastActivities::ORDER && !Configuration::get('WALLET_NOTIFICATION_ORDER'))
            || ($id_operation == PrepaymentLastActivities::REFUND && !Configuration::get('WALLET_NOTIFICATION_REFUND'))
            || ($id_operation == PrepaymentLastActivities::GIFT && !Configuration::get('WALLET_NOTIFICATION_GIFT'))) {
            return false;
        }

        $customer = new Customer((int)$id_customer);
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        if (!Validate::isFloat($old_wallet_balance)
            || !Validate::isFloat($new_wallet_balance)) {
            return false;
        }

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Currency::getCurrencyInstance((int)$id_currency);

        $old_wallet_balance = Tools::convertPriceFull($old_wallet_balance, $from_currency, $to_currency);
        $new_wallet_balance = Tools::convertPriceFull($new_wallet_balance, $from_currency, $to_currency);
        $round  = version_compare('_PS_VERSION_', '1.6', '>=') ? _PS_PRICE_COMPUTE_PRECISION_ : 2;
        $amount = Tools::ps_round($new_wallet_balance - $old_wallet_balance, $round);

        if ($amount > 0) {
            $movement_operation = $this->l('credited', false, $customer->id_lang);
        } elseif ($amount < 0) {
            $movement_operation = $this->l('debited', false, $customer->id_lang);
        } else {
            return false;
        }

        if ($id_operation == PrepaymentLastActivities::DEPOSIT) {
            $movement_type = $this->l('Deposit', false, $customer->id_lang);
        } elseif ($id_operation == PrepaymentLastActivities::CUSTOM_DEPOSIT) {
            $movement_type = $this->l('Manual deposit', false, $customer->id_lang);
        } elseif ($id_operation == PrepaymentLastActivities::DISBURSEMENT) {
            $movement_type = $this->l('Disbursement', false, $customer->id_lang);
        } elseif ($id_operation == PrepaymentLastActivities::CUSTOM_DISBURSEMENT) {
            $movement_type = $this->l('Manual disbursement', false, $customer->id_lang);
        } elseif ($id_operation == PrepaymentLastActivities::ORDER) {
            $movement_type = $this->l('Order', false, $customer->id_lang);
        } elseif ($id_operation == PrepaymentLastActivities::REFUND) {
            $movement_type = $this->l('Refund', false, $customer->id_lang);
        } elseif ($id_operation == PrepaymentLastActivities::GIFT) {
            $movement_type = $this->l('Gift', false, $customer->id_lang);
        } else {
            return false;
        }

        $template_vars = array(
            '{customer}' => $customer->firstname.' '.$customer->lastname,
            '{movement_amount}' => Tools::displayPrice(abs($amount), $to_currency->id),
            '{movement_operation}' => $movement_operation,
            '{movement_type}' => $movement_type,
            '{wallet_balance_old}' => Tools::displayPrice($old_wallet_balance, $to_currency->id),
            '{wallet_balance_new}' => Tools::displayPrice($new_wallet_balance, $to_currency->id)
        );



        if (!Mail::Send(
            $customer->id_lang,
            'wallet_notification',
            $this->l('Wallet notification', false, $customer->id_lang),
            $template_vars,
            $customer->email,
            $customer->firstname.' '.$customer->lastname,
            null,
            null,
            null,
            null,
            dirname(__FILE__).'/mails/'
        )) {
            return false;
        }

        return true;
    }

    public function l($string, $specific = false, $id_lang = 0)
    {
        if (!$id_lang || $id_lang == Context::getContext()->language->id) {
            return parent::l($string, $specific);
        }

        global $_MODULE;

        $lang_cache = array();
        $translations_merged = array();

        $language = new Language((int)$id_lang);
        if (!Validate::isLoadedObject($language)) {
            $language = Context::getContext()->language;
        }

        if (!isset($translations_merged[$this->name]) && isset($language)) {
            $files_by_priority = array(
                // Translations in theme
                _PS_THEME_DIR_.'modules/'.$this->name.'/translations/'.$language->iso_code.'.php',
                _PS_THEME_DIR_.'modules/'.$this->name.'/'.$language->iso_code.'.php',
                // PrestaShop 1.5 translations
                _PS_MODULE_DIR_.$this->name.'/translations/'.$language->iso_code.'.php',
                // PrestaShop 1.4 translations
                _PS_MODULE_DIR_.$this->name.'/'.$language->iso_code.'.php'
            );
            foreach ($files_by_priority as $file) {
                if (file_exists($file)) {
                    include_once($file);
                    $translations_merged[$this->name] = true;
                }
            }
        }
        $string = preg_replace("/\\\*'/", "\'", $string);
        $key = md5($string);
        $cache_key = $this->name.'|'.$string.'|'.(int)$language->id;

        if (!isset($lang_cache[$cache_key])) {
            $current_key = Tools::strtolower('<{'.$this->name.'}'._THEME_NAME_.'>'.$this->name).'_'.$key;
            $default_key = Tools::strtolower('<{'.$this->name.'}prestashop>'.$this->name).'_'.$key;
            if (!empty($_MODULE[$current_key])) {
                $ret = Tools::stripslashes($_MODULE[$current_key]);
            } elseif (!empty($_MODULE[$default_key])) {
                $ret = Tools::stripslashes($_MODULE[$default_key]);
            }

            $ret = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');
            $lang_cache[$cache_key] = $ret;
        }

        return $lang_cache[$cache_key];
    }
}
