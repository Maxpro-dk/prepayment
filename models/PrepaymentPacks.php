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

class PrepaymentPacks extends ObjectModel
{
    public $id_product;

    public $name;

    public $id_currency;

    public $credits;

    public $extra_credits;

    public $active;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'prepayment_packs',
        'primary' => 'id_prepayment_packs',
        'multilang' => true,
        'fields' => array(
            'id_product' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'name' =>                array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
            'id_currency' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'credits' =>            array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'extra_credits' =>        array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'active' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getPacks($active = null, $limit = null)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT pp.*
		FROM `'._DB_PREFIX_.'prepayment_packs` pp
		'.(isset($active) ? ' WHERE pp.active = '.$active : '').'
		ORDER BY pp.`credits` ASC'
        .(isset($limit) ? ' LIMIT '.(int)$limit : ''));

        if (!$res) {
            return array();
        }

        return $res;
    }

    public static function getPackInstance($id_product)
    {
        $id_pack = Db::getInstance()->getValue('
		SELECT `id_prepayment_packs`
		FROM `'._DB_PREFIX_.'prepayment_packs`
		WHERE `id_product` = '.(int)$id_product);

        $pack = new PrepaymentPacks((int)$id_pack);
        if (Validate::isLoadedObject($pack)) {
            return $pack;
        }

        return false;
    }

    public static function packExists($id_product)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT `id_prepayment_packs`
		FROM `'._DB_PREFIX_.'prepayment_packs`
		WHERE `id_product` = '.(int)$id_product);
    }

    public static function countPack($products)
    {
        if (!(is_array($products) && count($products))) {
            return false;
        }

        foreach ($products as $product) {
            if (self::packExists((int)$product['id_product'])) {
                return true;
            }
        }

        return false;
    }

    public static function getCredits($id_product)
    {
        return Db::getInstance()->getValue('
		SELECT pp.`credits`
		FROM `'._DB_PREFIX_.'prepayment_packs` pp
		WHERE pp.`id_product` = '.(int)$id_product);
    }

    public static function getExtraCredits($id_product)
    {
        return Db::getInstance()->getValue('
		SELECT pp.`extra_credits`
		FROM `'._DB_PREFIX_.'prepayment_packs` pp
		WHERE pp.`id_product` = '.(int)$id_product);
    }

    public static function getStats()
    {
        $result = array();
        $ids_product = array();

        $packs = self::getPacks();
        $count_packs  = count($packs);

        /* Get bought packs and best sale pack */
        foreach ($packs as $pack) {
            $ids_product[] = $pack['id_product'];
        }

        $ids_product = count($ids_product) ? implode(',', $ids_product) : '';
        if (!empty($ids_product)) {
            $bought_packs = Db::getInstance()->getValue('
			SELECT ps.`quantity`
			FROM `'._DB_PREFIX_.'product_sale` ps
			WHERE ps.`id_product` IN ('.$ids_product.')');
            $best_sale = Db::getInstance()->getRow('
			SELECT ps.`id_product`, pl.name
			FROM `'._DB_PREFIX_.'product_sale` ps
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl on ps.`id_product` = pl.`id_product`
			WHERE ps.`quantity`= (
				SELECT MAX(ps.`quantity`)
				FROM `'._DB_PREFIX_.'product_sale` ps
				WHERE ps.`id_product` IN ('.$ids_product.')
				)
			AND ps.`id_product` IN ('.$ids_product.')');
        }

        if (!isset($bought_packs) || !$bought_packs) {
            $bought_packs = 0;
        }

        if (!isset($best_sale) || !$best_sale) {
            $best_sale = array();
        }

        /* Get average unit price*/
        $credits = Db::getInstance()->getValue('
		SELECT SUM(pp.`credits`)
		FROM `'._DB_PREFIX_.'prepayment_packs` pp');

        $extra_credits = Db::getInstance()->getValue('
		SELECT SUM(pp.`extra_credits`)
		FROM `'._DB_PREFIX_.'prepayment_packs` pp');

        $average_credits = $count_packs > 0 ? ($credits + $extra_credits) / $count_packs : 0;

        /* Get unactive packs */
        $unactive_packs = Db::getInstance()->getValue('
		SELECT COUNT(`id_prepayment_packs`)
		FROM `'._DB_PREFIX_.'prepayment_packs`
		WHERE `active` = 0');

        /* Add stats into an array */
        $result['bought_packs'] = $bought_packs;
        $result['best_sale'] = $best_sale;
        $result['average_credits'] = $average_credits;
        $result['unactive_packs'] = $unactive_packs;

        return $result;
    }
}
