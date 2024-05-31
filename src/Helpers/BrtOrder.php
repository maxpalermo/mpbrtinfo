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

namespace MpSoft\MpBrtInfo\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BrtOrder
{
    public static function getOrdersIdByIdOrderStates($id_order_states, $limit = 20)
    {
        $sql = 'SELECT `id_order` FROM `' . _DB_PREFIX_ . 'orders` WHERE `current_state` IN (' . implode(',', array_map('intval', $id_order_states)) . ')';
        $sql .= ' ORDER BY id_order DESC';
        if ($limit) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        $result = \Db::getInstance()->executeS($sql);
        $orders = [];
        foreach ($result as $row) {
            $orders[] = $row['id_order'];
        }

        return $orders;
    }

    public static function getOrdersIdExcludingOrderStates($id_order_states, $orderHistory = [], $limit = 20)
    {
        $db = \Db::getInstance();
        $carriers = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS);
        if ($carriers) {
            $carriers = array_map(function ($item) {
                return "'" . pSQL($item) . "'";
            }, $carriers);
            $carriers = implode(',', $carriers);
            $query = 'SELECT `id_carrier` FROM `' . _DB_PREFIX_ . "carrier` WHERE `name` IN ($carriers) and deleted = 0 and active = 1";
            $result = $db->executeS($query);
            if ($result) {
                $carriers = [];
                foreach ($result as $row) {
                    $carriers[] = $row['id_carrier'];
                }
                $carriers = implode(',', $carriers);
            }
        }

        $query = 'SELECT id_order FROM `'
            . _DB_PREFIX_ . 'orders` '
            . 'WHERE `current_state` NOT IN (' . implode(',', $id_order_states) . ') '
            . 'AND id_order NOT IN (' . implode(',', $orderHistory) . ') '
            . 'AND `id_carrier` IN (' . $carriers . ') '
            . 'ORDER BY id_order DESC';
        if ($limit) {
            $query .= ' LIMIT ' . (int) $limit;
        }

        $result = $db->executeS($query);
        if ($result) {
            $id_orders = array_column($result, 'id_order');
        } else {
            $id_orders = [];
        }

        return $id_orders;
    }

    public static function getOrdersHistoryIdExcludingOrderStates($id_order_states, $limit = 20)
    {
        $db = \Db::getInstance();
        $id_order_states = array_map('intval', $id_order_states);

        $sql = 'SELECT `id_order`, `id_brt_state`, `id_order_state`, `date_add` '
            . 'FROM `' . _DB_PREFIX_ . \ModelBrtTrackingNumber::$definition['table'] . '` '
            . 'WHERE `id_order_state` NOT IN (' . implode(',', $id_order_states) . ') '
            . 'GROUP BY `id_order` '
            . 'HAVING `date_add` = MAX(`date_add`) '
            . 'ORDER BY id_order DESC ';
        if ($limit) {
            $sql .= 'LIMIT ' . (int) $limit;
        }
        $result = \Db::getInstance()->executeS($sql);
        $orders = [];
        foreach ($result as $row) {
            $orders[] = $row['id_order'];
        }

        return $orders;
    }

    public static function getOrdersReferenceByIdOrderStates($id_order_states, $limit = 20)
    {
        $sql = 'SELECT `reference` FROM `' . _DB_PREFIX_ . 'orders` WHERE `current_state` IN (' . implode(',', array_map('intval', $id_order_states)) . ')';
        $sql .= ' ORDER BY id_order DESC';
        if ($limit) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        $result = \Db::getInstance()->executeS($sql);
        $orders = [];
        foreach ($result as $row) {
            $orders[] = $row['id_order'];
        }

        return $orders;
    }

    public static function getOrderTracking($id_order)
    {
        $sql = new \DbQuery();
        $sql->select('tracking_number');
        $sql->from('order_carrier');
        $sql->where('id_order=' . (int) $id_order);
        $sql->orderBy('id_order_carrier DESC');

        return \Db::getInstance()->getValue($sql);
    }
}
