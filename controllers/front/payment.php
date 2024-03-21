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

class PrepaymentPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $id_customer = (int)$this->context->customer->id;
        if (!($wallet = PrepaymentWallets::getWalletInstance($id_customer))) {
            Tools::redirect(Context::getContext()->link->getModuleLink('prepayment', 'dashboard'));
        }

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Context::getContext()->currency;
        $balance = Tools::convertPriceFull($wallet->total_amount, $from_currency, $to_currency);

        $this->context->smarty->assign(array(
            'balance' => $balance,
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'isoCode' => $this->context->language->iso_code,
            'this_path' => $this->module->getPathUri(),
            'errors' => $this->errors,
            'this_path_prepayment' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));

        $this->setTemplate('payment_execution.tpl');
    }
}
