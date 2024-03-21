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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_0($object)
{
    $languages = Language::getLanguages();

    // Add new hook
    if (!$object->registerHook('displayTop')) {
        return false;
    }

    // Install  new conf
    Configuration::updateValue('WALLET_PACKS_CAT', Configuration::get('PS_PREPAYMENT_PACKS_CAT'));
    Configuration::updateValue('WALLET_DEFAULT_CURRENCY_PACKS', Configuration::get('PS_CURRENCY_DEFAULT'));
    Configuration::updateValue('WALLET_ALLOW_NEGATIVE_BALANCE', Configuration::get('PS_PREPAYMENT_ALLOW_NEGATIVE_BALANCE'));
    Configuration::updateValue('WALLET_NEGATIVE_BALANCE_MAX', Configuration::get('PS_PREPAYMENT_NEGATIVE_BALANCE_MAXIMUM'));
    Configuration::updateValue('WALLET_ALLOW_DISBURSEMENT', Configuration::get('PS_PREPAYMENT_ALLOW_DISBURSEMENT'));
    Configuration::updateValue('WALLET_AUTO_REFUND', Configuration::get('PS_PREPAYMENT_AUTO_REFUND_IN_WALLET'));
    Configuration::updateValue('WALLET_DISPLAY_PACKS', Configuration::get('PS_PREPAYMENT_DISPLAY_PACKS'));
    Configuration::updateValue('WALLET_DISPLAY_GIFTS', Configuration::get('PS_PREPAYMENT_DISPLAY_GIFTS'));
    Configuration::updateValue('WALLET_DISPLAY_TOPMENU', 0);
    Configuration::updateValue('WALLET_AUTO_OPEN', Configuration::get('PS_PREPAYMENT_AUTO_OPEN_WALLET'));

    // Delete deprecated conf
    Configuration::deleteByName('PS_PREPAYMENT_PACKS_CAT');
    Configuration::deleteByName('PS_PREPAYMENT_ALLOW_NEGATIVE_BALANCE');
    Configuration::deleteByName('PS_PREPAYMENT_NEGATIVE_BALANCE_MAXIMUM');
    Configuration::deleteByName('PS_PREPAYMENT_ALLOW_DISBURSEMENT');
    Configuration::deleteByName('PS_PREPAYMENT_AUTO_REFUND_IN_WALLET');
    Configuration::deleteByName('PS_PREPAYMENT_DISPLAY_PACKS');
    Configuration::deleteByName('PS_PREPAYMENT_DISPLAY_GIFTS');
    Configuration::deleteByName('PS_PREPAYMENT_AUTO_OPEN_WALLET');

    // Delete deprecated mysql columns
    if (!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'prepayment_packs` DROP `priceTE`')
        || !Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'prepayment_packs` DROP `priceTI`')
        || !Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'prepayment_packs` DROP `id_tax_rules_group`')) {
        return false;
    }

    // delete old tabs
    $tabs = Tab::getCollectionFromModule($object->name);
    foreach ($tabs as $tab) {
        if (!$tab->delete()) {
            return false;
        }
    }

    // Install new tabs
    foreach ($object->tabs as $tab) {
        $obj = new Tab();
        foreach ($languages as $lang) {
            $obj->name[$lang['id_lang']] = $object->l($tab['name']);
        }
        $obj->class_name = $tab['className'];
        $obj->id_parent = $tab['className'] == 'AdminPrepaymentDashboard' ? (int)Tab::getIdFromClassName('AdminParentCustomer') : $tab['id_parent'];
        $obj->module = $object->name;
        if (!$obj->add()) {
            return false;
        }
    }

    // delete old meta
    foreach ($object->metas as $meta) {
        if (!$metas = Meta::getMetaByPage('module-'.$object->name.'-'.$meta['controller'], (int)Context::getContext()->language->id)) {
            continue;
        }
        $obj = new Meta((int)$metas['id_meta']);
        if (!$obj->delete()) {
            return false;
        }
    }

    // Install new meta
    foreach ($object->metas as $meta) {
        $obj = new Meta();
        $obj->page = 'module-'.$object->name.'-'.$meta['controller'];
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

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
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

    // delete deprecated files
    $files = array(
        _PS_MODULE_DIR_.$object->name.'/css',
        _PS_MODULE_DIR_.$object->name.'/img',
        _PS_MODULE_DIR_.$object->name.'/js',
        _PS_MODULE_DIR_.$object->name.'/controllers/admin/AdminPrepaymentSettingController.php',
        _PS_MODULE_DIR_.$object->name.'/controllers/admin/AdminPrepaymentWalletController.php',
        _PS_MODULE_DIR_.$object->name.'/controllers/front/wallet.php',
        _PS_MODULE_DIR_.$object->name.'/controllers/front/buycredits.php',
        _PS_MODULE_DIR_.$object->name.'/views/templates/admin/prepayment_last_activities',
        _PS_MODULE_DIR_.$object->name.'/views/templates/admin/prepayment_packs',
        _PS_MODULE_DIR_.$object->name.'/views/templates/admin/prepayment_wallet',
        _PS_MODULE_DIR_.$object->name.'/views/templates/admin/prepayment_wallet',
        _PS_MODULE_DIR_.$object->name.'/views/templates/front/wallet.tpl',
        _PS_MODULE_DIR_.$object->name.'/views/templates/front/buy-credits.tpl',
        _PS_MODULE_DIR_.$object->name.'/views/templates/hook/admin_wallet_account.tpl',
        _PS_MODULE_DIR_.$object->name.'/views/templates/hook/block-packs.tpl',
        _PS_MODULE_DIR_.$object->name.'/views/templates/hook/productTabPack.tpl'
    );

    foreach ($files as $file) {
        if (file_exists($file)) {
            deleteFile($file);
        }
    }

    return true;
}

function deleteFile($path)
{
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            deleteFile($path.DIRECTORY_SEPARATOR.$file);
        }

        return rmdir($path);
    } elseif (is_file($path) === true) {
        return unlink($path);
    } else {
        return false;
    }
}
