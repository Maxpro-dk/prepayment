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

class PrepaymentDepositsModuleFrontController extends ModuleFrontController
{
    public function display()
    {
        $scope = $this->context->smarty->createData($this->context->smarty);
        $scope->assign(array(
            'errors' => $this->errors,
            'request_uri' => Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']))
        ));
        $tpl_errors = version_compare(_PS_VERSION_, '1.7', '<') ? _PS_THEME_DIR_.'/errors.tpl' : '_partials/form-errors.tpl';
        $errors_rendered = $this->context->smarty->createTemplate($tpl_errors, $scope)->fetch();

        $this->context->smarty->assign(array(
            'errors_rendered' => $errors_rendered,
            'errors_nb' => (int)count($this->errors),
            'token' => Tools::getToken(false),
            'currentUrl' => $this->context->link->getModuleLink('prepayment', 'deposits')
        ));

        $template = version_compare(_PS_VERSION_, '1.7', '>=') ? 'module:prepayment/views/templates/front/layout/deposits.tpl' : 'deposits.tpl';
        $this->setTemplate($template);

        return parent::display();
    }

    public function initContent()
    {
        parent::initContent();

        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Context::getContext()->currency;
        $packs = PrepaymentPacks::getPacks(true);
        foreach ($packs as &$pack) {
            $product = new Product((int)$pack['id_product']);
            $pack['name'] = $product->name[(int)$this->context->language->id];
            $pack['credits'] = Tools::convertPriceFull($pack['credits'], $from_currency, $to_currency);
            $pack['extra_credits'] = Tools::convertPriceFull($pack['extra_credits'], $from_currency, $to_currency);
        }

        $this->context->smarty->assign(array(
            'packs' => $packs,
        ));
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->context->controller->addJS(_MODULE_DIR_.'prepayment/views/js/front/deposits.js');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }
}
