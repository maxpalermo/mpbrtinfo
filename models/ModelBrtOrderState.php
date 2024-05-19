<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class ModelBrtOrderState extends ObjectModel
{
    protected static $errors = [];
    public $id_order;
    public $id_brt_state;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mp_brtinfo_order_state',
        'primary' => 'id_brtinfo_order_state',
        'multilang' => false,
        'fields' => [
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_brt_state' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
        ],
    ];

    public static function getOrdersByIdState($id_state, $limit = 20)
    {
        $sql = 'SELECT '
            . 'o.total_paid_tax_incl,o.date_add,'
            . 'c.firstname, c.lastname, c.email, '
            . 'ad.phone, ad.phone_mobile, '
            . 'e.id_evento as order_state_code, e.name as order_state_name '
            . 'FROM ' . _DB_PREFIX_ . 'mp_brtinfo_order_state a '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON a.id_order = o.id_order '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON o.id_customer = c.id_customer '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'address ad ON o.id_address_delivery = ad.id_address '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'mpbrtinfo_evento e ON a.id_brt_state = e.id_mpbrtinfo_evento '
            . 'WHERE a.id_brt_state = ' . (int) $id_state
            . ' ORDER BY o.date_add DESC '
            . 'LIMIT ' . (int) $limit;

        return Db::getInstance()->executeS($sql);
    }

    public static function insertOrderState($id_order, $id_brt_state, $date_add)
    {
        if (!$id_order) {
            return false;
        }

        if (!$id_brt_state) {
            return false;
        }

        if (!$date_add) {
            $date_add = date('Y-m-d H:i:s');
        }

        try {
            $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'mp_brtinfo_order_state '
                . '(id_order, id_brt_state, date_add) '
                . 'VALUES (' . (int) $id_order . ', ' . (int) $id_brt_state . ', "' . pSQL($date_add) . '")';
            $res = Db::getInstance()->execute($sql);
            if (!$res) {
                self::$errors[] = Db::getInstance()->getMsgError();

                return false;
            }
        } catch (\Throwable $th) {
            self::$errors[] = $th->getMessage();

            return false;
        }

        return true;
    }

    public static function getErrors()
    {
        return self::$errors;
    }

    public static function getOrderState($state)
    {
        $check = 'is_' . strtolower($state);
        $sql = 'SELECT id_mpbrtinfo_evento FROM ' . _DB_PREFIX_ . 'mpbrtinfo_evento '
            . "WHERE `{$check}` = 1";

        $res = Db::getInstance()->executeS($sql);
        if ($res) {
            $out = [];
            foreach ($res as $key => $value) {
                $out[] = $value['id_mpbrtinfo_evento'];
            }

            return $out;
        }

        return [];
    }
}
