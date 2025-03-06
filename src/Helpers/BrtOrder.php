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

use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByRMN;

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
        $carriers = \ModelBrtConfig::getCarriers();
        if ($carriers) {
            $carriers = implode(',', $carriers);
        } else {
            return [];
        }

        if (!$orderHistory) {
            $orderHistory = [0];
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

    public static function checkSkipped()
    {
        $id_order_state_skip = json_decode(\Configuration::get(\ModelBrtConfig::MP_BRT_INFO_OS_SKIP), true);
        if (!$id_order_state_skip) {
            return [];
        }

        // Prelevo tutti gli ordini che fanno parte degli stati da saltare
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_order')
            ->from('orders')
            ->where('current_state in(' . implode(',', $id_order_state_skip) . ')')
            ->where('DATEDIFF(NOW(), `date_add`) < 15')
            ->orderBy('date_add DESC');
        $rows = $db->executeS($sql);

        if ($rows) {
            return $rows;
        }

        return [];
    }

    public static function checkDelivered($id_order_state_delivered = null)
    {
        if (!$id_order_state_delivered) {
            $id_order_state_delivered = json_decode(\Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED), true);
        }
        $max_days = 15;
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_order, id_carrier')
            ->from('orders')
            ->where('current_state in(' . implode(',', $id_order_state_delivered) . ')')
            ->where('DATEDIFF(NOW(), `date_add`) < ' . $max_days)
            ->orderBy('date_add DESC');
        $rows = $db->executeS($sql);
        $delivered = (int) \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
        //TODO:: get the BRT delivered id from the config
        $brt_delivered = 704;

        if ($rows) {
            foreach ($rows as $row) {
                $sql = new \DbQuery();
                $sql->select('count(*)')
                    ->from('mpbrtinfo_tracking_number')
                    ->where('id_order_state IN (' . implode(',', $id_order_state_delivered) . ')')
                    ->where('id_order = ' . (int) $row['id_order']);
                $result = (int) $db->getValue($sql );
                if ($result) {
                    continue;
                }

                $tracking = self::getIdSpedizioneByRMN(self::getRMN($row['id_order']));
                if ($tracking) {
                    // inserisco il tracking in order_carrier
                    $sql = new \DbQuery();
                    $sql->select('id_order')
                        ->from('order_carrier')
                        ->where('id_order = ' . (int) $row['id_order'])
                        ->where('id_carrier = ' . (int) $brt_delivered);
                    $result = $db->getValue($sql);
                    if (!$result) {
                        $model = new \ModelBrtOrderCarrier();
                        $model->id_order = $row['id_order'];
                        $model->id_carrier = $brt_delivered;
                        $model->add();
                    }
                }

                $model = new \ModelBrtTrackingNumber();
                $model->id_order = $row['id_order'];
                $model->id_order_state = $delivered;
                $model->date_event = date('Y-m-d H:i:s');
                $model->id_brt_state = $brt_delivered;
                $model->id_collo = str_pad($tracking, 12, '0', STR_PAD_LEFT) ;
                $model->rmn = self::getRMN($row['id_order']);
                $model->rma = self::getRMA($row['id_order']);
                $model->tracking_number = str_pad($tracking, 12, '0', STR_PAD_LEFT);
                $model->current_state = 'DELIVERED';
                $model->anno_spedizione = date('Y');
                $model->date_shipped = date('Y-m-d H:i:s');
                $model->date_delivered = date('Y-m-d H:i:s');
                $model->days = 0;

                $model->add();
            }
        }
    }

    public static function getRMN($id_order)
    {
        if (\ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE == 'ID')) {
            return $id_order;
        }

        $db = \Db::getInstance();
        $sql = 'SELECT reference FROM ' . _DB_PREFIX_ . 'orders WHERE id_order = ' . (int) $id_order;

        return $db->getValue($sql);
    }

    public static function getRMA($id_order)
    {
        if (\ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE == 'ID')) {
            return $id_order;
        }

        $db = \Db::getInstance();
        $sql = 'SELECT reference FROM ' . _DB_PREFIX_ . 'orders WHERE id_order = ' . (int) $id_order;

        return $db->getValue($sql);
    }

    public static function getIdSpedizioneByRMN($rmn)
    {
        $brt_customer_id = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER);
        $class = new BrtSoapClientIdSpedizioneByRMN();
        $result = $class->getSoapIdSpedizioneByRMN($brt_customer_id, $rmn);

        if ($result === false) {
            return '';
        }

        return $result['spedizione_id'];
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
