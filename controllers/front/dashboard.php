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

class PrepaymentDashboardModuleFrontController extends ModuleFrontController
{
    public $auth = true;

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
            'currentUrl' => $this->context->link->getModuleLink('prepayment', 'dashboard')
        ));

        $template = version_compare(_PS_VERSION_, '1.7', '>=') ? 'module:prepayment/views/templates/front/layout/dashboard.tpl' : 'dashboard.tpl';
        $this->setTemplate($template);

        return parent::display();
    }

    public function initContent()
    {
        parent::initContent();

        $id_customer = (int)$this->context->customer->id;
        $wallet = PrepaymentWallets::getWalletInstance($id_customer);
        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Context::getContext()->currency;

        $balance = $wallet ? Tools::displayPrice(Tools::convertPriceFull($wallet->total_amount, $from_currency, $to_currency), $to_currency) : false;
        $is_active = $wallet ? $wallet->active : false;

        $last_activities = PrepaymentLastActivities::getLastActivities($id_customer);
        foreach ($last_activities as &$last_activity) {
            if ($last_activity['id_operation'] == PrepaymentLastActivities::DEPOSIT) {
                $last_activity['operation'] = $this->module->l('Deposit');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::ORDER) {
                $last_activity['operation'] = $this->module->l('Order');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::REFUND) {
                $last_activity['operation'] = $this->module->l('Refund');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::DISBURSEMENT) {
                $last_activity['operation'] = $this->module->l('Disbursement');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::GIFT) {
                $last_activity['operation'] = $this->module->l('Gift');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::CUSTOM_DEPOSIT) {
                $last_activity['operation'] = $this->module->l('Manual deposit');
            } elseif ($last_activity['id_operation'] == PrepaymentLastActivities::CUSTOM_DISBURSEMENT) {
                $last_activity['operation'] = $this->module->l('Manual disbursement');
            }

            $last_activity['display_price'] = Tools::displayPrice($last_activity['price'], Currency::getCurrencyInstance($last_activity['id_currency']));

            $total_credits = Tools::convertPriceFull($last_activity['credits'] + $last_activity['extra_credits'], Currency::getCurrencyInstance($last_activity['id_currency']), $to_currency);
            $last_activity['display_credits'] = Tools::displayPrice($total_credits, $to_currency);

            $last_activity['is_partial'] = false;
            if (PrepaymentLastActivities::isPartial($last_activity['id_prepayment_last_activities'])) {
                $last_activity_obj = new PrepaymentLastActivities((int)$last_activity['id_prepayment_last_activities']);
                $id_partial = $last_activity_obj->getPartialId();
                $partial_obj = new PrepaymentPartials((int)$id_partial);
                if (Validate::isLoadedObject($partial_obj) && !Order::getOrderByCartId((int)$partial_obj->id_cart)) {
                    $last_activity['link_order'] = $this->context->link->getPageLink(
                        'order',
                        false,
                        (int)$this->context->cart->id_lang,
                        'step=3&recover_cart='.(int)$partial_obj->id_cart.'&token_cart='.md5(_COOKIE_KEY_.'recover_cart_'.(int)$partial_obj->id_cart)
                    );

                    $last_activity['is_partial'] = true;
                    $last_activity['partial'] = $last_activity['price'] - $total_credits;
                    $last_activity['display_partial'] = Tools::displayPrice($last_activity['partial'], $to_currency);
                }
            }
        }

        $this->context->smarty->assign(array(
            'is_active' => $is_active,
            'balance' => $balance,
            'history_list' => $last_activities
        ));
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitWallet')) {
            $id_customer = (int)$this->context->customer->id;
            if (PrepaymentWallets::walletExists($id_customer)) {
                $this->errors[] = Tools::displayError('A wallet is already opened for this account.');
            } else {
                $wallet = new PrepaymentWallets();
                $wallet->id_customer = $id_customer;
                $wallet->total_amount = 0;
                $wallet->active = 1;
                $wallet->add();

                Tools::redirect($this->context->link->getModuleLink('prepayment', 'dashboard'));
            }
        } elseif ($id_last_activity = (int)Tools::getValue('deletePartial')) {
            $id_customer = (int)$this->context->customer->id;
            $wallet = PrepaymentWallets::getWalletInstance($id_customer);
            if (!$wallet) {
                $this->errors[] = Tools::displayError('Wallet cannot be loaded.');
            } else {
                $last_activity = new PrepaymentLastActivities((int)$id_last_activity);
                if (!Validate::isLoadedObject($last_activity)) {
                    $this->errors[] = Tools::displayError('This partial payment does not exist.');
                } else {
                    if ((int)$last_activity->id_wallet !== (int)$wallet->id) {
                        $this->errors[] = Tools::displayError('An error occured while loading your wallet.');
                    } else {
                        $id_partial = (int)$last_activity->getPartialId();
                        $partial = new PrepaymentPartials((int)$id_partial);
                        if (!Validate::isLoadedObject($partial)) {
                            $this->errors[] = Tools::displayError('Partial payment cannot be loaded');
                        } else {
                            $cart_rule = new CartRule((int)$partial->id_cart_rule);
                            if (!Validate::isLoadedObject($cart_rule)) {
                                $this->errors[] = Tools::displayError('This partial payment does not exist.');
                            } else {
                                $last_activity->delete();
                                Context::getContext()->cart->removeCartRule((int)$cart_rule->id);
                                $cart_rule->delete();
                                $partial->delete();
                            }
                        }
                    }
                }
            }
        }
        return parent::postProcess();
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }
}
