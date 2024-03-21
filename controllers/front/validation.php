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

class PrepaymentValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'prepayment'));
        }

        if (PrepaymentPartials::partialExists((int)$cart->id)) {
            Tools::redirect(Context::getContext()->link->getPageLink('order', true, null, 'step=3'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        if (PrepaymentPacks::countPack($cart->getProducts())) {
            Tools::redirect(Context::getContext()->link->getPageLink('order', true, null, 'step=3'));
        }

        if (!($wallet = PrepaymentWallets::getWalletInstance((int)$customer->id))) {
            Tools::redirect(Context::getContext()->link->getModuleLink('prepayment', 'deposits'));
        }

        if (!$wallet->active) {
            Tools::redirect(Context::getContext()->link->getModuleLink('prepayment', 'dashboard'));
        }

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $currency = $this->context->currency;
        $balance = Tools::convertPriceFull($wallet->total_amount + Configuration::get('WALLET_NEGATIVE_BALANCE_MAX'), $from_currency, $currency);

        if ($total > $balance) {
            if (Configuration::get('WALLET_PARTIAL_PAYMENT') && ($balance > 0)) {
                // calculate credits (shipping cost cannot be deducted for partial payment)
                $total_products = $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
                $credits = $total_products < $balance ? $total_products : $balance;

                // debiting wallet
                $last_activity = new PrepaymentLastActivities();
                $last_activity->id_order = 0;
                $last_activity->id_wallet = (int)$wallet->id;
                $last_activity->id_currency = (int)$cart->id_currency;
                $last_activity->id_customer = (int)$customer->id;
                $last_activity->price = $total;
                $last_activity->credits = $credits;
                $last_activity->extra_credits = 0;
                $last_activity->id_operation = PrepaymentLastActivities::ORDER;
                $last_activity->paid = 1;
                $last_activity->add();

                // creating cart rule for discount
                $languages = Language::getLanguages(true);
                $cart_rule = new CartRule();
                foreach ($languages as $language) {
                    if ($language['iso_code'] == 'fr') {
                        $cart_rule->name[(int)$language['id_lang']] = 'Paiement partiel';
                    } else {
                        $cart_rule->name[(int)$language['id_lang']] = 'Partial payment';
                    }
                }
                $cart_rule->description = 'Cart '.(int)$cart->id;
                $cart_rule->partial_use = false;
                $cart_rule->id_customer = (int)$customer->id;
                $cart_rule->code = Tools::strtoupper(Tools::passwdGen(12));
                $cart_rule->date_from = date('Y-m-d');
                $cart_rule->date_to = date('Y-m-d', strtotime('+1 year', strtotime($cart_rule->date_from)));
                $cart_rule->reduction_amount = $credits;
                $cart_rule->reduction_tax = true;
                $cart_rule->reduction_currency = (int)$cart->id_currency;
                $cart_rule->add();

                $this->context->cart->addCartRule((int)$cart_rule->id);

                // creating partial payment
                $partial = new PrepaymentPartials();
                $partial->id_prepayment_last_activities = (int)$last_activity->id;
                $partial->id_cart = (int)$cart->id;
                $partial->id_cart_rule = (int)$cart_rule->id;
                $partial->active = true;
                $partial->add();

                Tools::redirect(Context::getContext()->link->getPageLink('order', true, null, 'step=3'));
            } else {
                Tools::redirect(Context::getContext()->link->getModuleLink('prepayment', 'deposits'));
            }
        }

        $this->module->validateOrder(
            (int)$cart->id,
            Configuration::get('PS_OS_PAYMENT'),
            $total,
            $this->module->displayName,
            null,
            array(),
            (int)$currency->id,
            false,
            $customer->secure_key
        );

        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }
}
