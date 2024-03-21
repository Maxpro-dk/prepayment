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

class AdminPrepaymentPacksController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->explicitSelect = true;
        $this->table = 'prepayment_packs';
        $this->className = 'PrepaymentPacks';
        $this->lang = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->fields_list = array(
            'id_prepayment_packs' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Product'),
                'havingFilter' => true,
            ),
            'credits' => array(
                'title' => $this->l('Credits'),
                'align' => 'text-right',
                'callback' => 'setPacksCurrency',
            ),
            'extra_credits' => array(
                'title' => $this->l('Extra Credits'),
                'align' => 'text-right',
                'callback' => 'setPacksCurrency',
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
        $pack = new PrepaymentPacks($tr['id_prepayment_packs']);
        if (!Validate::isLoadedObject($pack)) {
            throw new PrestaShopException('object Last Activities can\'t be loaded');
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
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_title = $this->l('Packs');
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminPrepaymentDashboard'),
            'desc' => $this->l('Back', null, null, false),
            'icon' => 'process-icon-back'
        );
        $this->page_header_toolbar_btn['new_packs'] = array(
            'href' => self::$currentIndex.'&addprepayment_packs&token='.$this->token,
            'desc' => $this->l('Add new pack', null, null, false),
            'icon' => 'process-icon-new'
        );
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );

        return parent::renderList();
    }

    public function renderForm()
    {
        $this->addJqueryPlugin('autocomplete');

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Prepayment packs'),
                'icon' => 'icon-truck'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'lang' => true,
                    'required' => true,
                    'autocomplete'=> false,
                    'col' => '4'
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
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Credits'),
                    'name' => 'credits',
                    'required' => false,
                    'autocomplete'=> false,
                    'class' => 'input-xlarge ac_input',
                    'suffix' => Currency::getCurrencyInstance(Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'))->sign,
                    'col' => '2'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Extra Credits'),
                    'name' => 'extra_credits',
                    'required' => false,
                    'autocomplete'=> false,
                    'class' => 'input-xlarge ac_input',
                    'suffix' => Currency::getCurrencyInstance(Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'))->sign,
                    'col' => '2'
                ),
            ),
            'help' => true,
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $id_pack = (int)Tools::getValue('id_prepayment_packs');
        if (!$id_pack && Validate::isLoadedObject($this->object)) {
            $id_pack = $this->object->id;
        }
        if ($id_pack) {
            $pack = new PrepaymentPacks((int)$id_pack);
        }


        $this->tpl_form_vars = array(
            'currencyPack' => isset($pack) ? Currency::getCurrencyInstance($pack->id_currency) : Currency::getCurrencyInstance(Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS')),
            'currency' => Currency::getCurrencyInstance(Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS')),
            'tax_rules_groups' => TaxRulesGroup::getTaxRulesGroups(true),
            'taxesRatesByGroup' => TaxRulesGroup::getAssociatedTaxRatesByIdCountry($this->context->country->id),
            'ecotaxTaxRate' => Tax::getProductEcotaxRate(),
            'tax_exclude_taxe_option' => Tax::excludeTaxeOption(),
            'ps_use_ecotax' => Configuration::get('PS_USE_ECOTAX')
        );

        return parent::renderForm();
    }

    public function processStatus()
    {
        parent::processStatus();
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            $product = new Product((int)$object->id_product);
            return $product->toggleStatus();
        }
        return false;
    }

    public function postProcess()
    {
        if (Tools::getValue('submitAdd'.$this->table)) {
            if (Tools::getValue('credits') <= 0) {
                $this->errors[] = Tools::displayError('Credits field must be higher than 0');
            }

            /* Checking fields validity */
            $this->validateRules();
            if (!count($this->errors)) {
                $id = (int)Tools::getValue('id_prepayment_packs');

                /* Object update */
                if (isset($id) && !empty($id)) {
                    try {
                        if ($this->tabAccess['edit'] == '1') {
                            $pack = new PrepaymentPacks($id);
                            if (Validate::isLoadedObject($pack)) {
                                $this->processProduct((int)$pack->id_product);
                                Tools::redirectAdmin(self::$currentIndex.'&id_prepayment_packs='.$pack->id.'&conf=4&token='.$this->token);
                            } else {
                                $this->errors[] = Tools::displayError('An error occurred while updating an object.').' <b>'.$this->table.'</b>';
                            }
                        } else {
                            $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                        }
                    } catch (PrestaShopException $e) {
                        $this->errors[] = $e->getMessage();
                    }
                } else {
                    if ($this->tabAccess['add'] == '1') {
                        $id_product = $this->processProduct();
                        if (!($pack = PrepaymentPacks::getPackInstance((int)$id_product))) {
                            $this->errors[] = Tools::displayError('An error occurred while adding an object.').' <b>'.$this->table.'</b>';
                        } else {
                            Tools::redirectAdmin(self::$currentIndex.'&id_prepayment_packs='.$pack->id.'&conf=3&token='.$this->token);
                        }
                    } else {
                        $this->errors[] = Tools::displayError('You do not have permission to add this.');
                    }
                }
            }
        } else {
            if (Tools::isSubmit('delete'.$this->table)) {
                if ($this->tabAccess['delete'] == '1') {
                    $id = (int)Tools::getValue('id_prepayment_packs');
                    $pack = new PrepaymentPacks($id);
                    $pack->delete();

                    $product = new Product((int)$pack->id_product);
                    $product->delete();

                    Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token);
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to delete this.');
                }
            } elseif (Tools::isSubmit($this->table.'Box') && count(Tools::isSubmit($this->table.'Box')) > 0) {
                if ($this->tabAccess['delete'] == '1') {
                    $ids = Tools::getValue($this->table.'Box');
                    array_walk($ids, 'intval');

                    foreach ($ids as $id) {
                        $pack = new PrepaymentPacks((int)$id);
                        $pack->delete();

                        $product = new Product((int)$pack->id_product);
                        $product->delete();
                    }

                    Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token);
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to delete this.');
                }
            }
        }
        return parent::postProcess();
    }

    public function processProduct($id_product = null)
    {
        $from_currency = Currency::getCurrencyInstance((int)Configuration::get('WALLET_DEFAULT_CURRENCY_PACKS'));
        $to_currency = Currency::getCurrencyInstance((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        $languages = Language::getLanguages();

        $product = new Product((int)$id_product);
        foreach ($languages as $language) {
            $product->name[(int)$language['id_lang']] = Tools::getValue('name_'.$language['id_lang']);
            $product->link_rewrite[(int)$language['id_lang']] = Tools::link_rewrite(Tools::getValue('name_'.$language['id_lang']));
        }
        $product->reference = Tools::getValue('credits') + Tools::getValue('extra_credits').'_credits';
        $product->active = Tools::getValue('active');
        $product->id_tax_rules_group = 0;
        $product->price = Tools::convertPriceFull(Tools::getValue('credits'), $from_currency, $to_currency);

        if (Validate::isLoadedObject($product)) {
            return $product->update();
        }

        $product->is_virtual = 1;
        $product->quantity = 5000;
        $product->visibility = 'none';
        $product->id_category_default = Configuration::get('WALLET_PACKS_CAT');
        $product->category = array(Configuration::get('WALLET_PACKS_CAT'));
        $product->add();
        $product->updateCategories($product->category);
        StockAvailable::setQuantity((int)$product->id, 0, $product->quantity, $this->context->shop->id);

        if (file_exists(_PS_MODULE_DIR_.$this->module->name.'/views/img/amount.jpg')) {
            $image = new Image();
            $image->id_product = (int)$product->id;
            $image->position = Image::getHighestPosition((int)$product->id) + 1;
            $image->legend[$this->context->language->id] = $this->l('amount');

            if (!Image::getCover($image->id_product)) {
                $image->cover = 1;
            } else {
                $image->cover = 0;
            }

            if (!$image->add()) {
                $this->errors[] = Tools::displayError('Error while creating additional image');
            } else {
                $file = _PS_MODULE_DIR_.$this->module->name.'/views/img/amount.jpg';
                if (!($new_path = $image->getPathForCreation())) {
                    $this->errors[] = Tools::displayError('An error occurred while attempting to create a new folder.');
                }
                if (!($tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !copy($file, $tmpName)) {
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                } elseif (!ImageManager::resize($tmpName, $new_path.'.'.$image->image_format)) {
                    $this->errors[] = Tools::displayError('An error occurred while copying the image.');
                }

                $imagesTypes = ImageType::getImagesTypes('products');
                foreach ($imagesTypes as $image_type) {
                    if (!ImageManager::resize($tmpName, $new_path.'-'.Tools::stripslashes($image_type['name']).'.'.$image->image_format, $image_type['width'], $image_type['height'], $image->image_format)) {
                        $this->errors[] = Tools::displayError('An error occurred while copying image:').' '.Tools::stripslashes($image_type['name']);
                    }
                }
                @unlink($tmpName);
            }
        }
        return $product->id;
    }
}
