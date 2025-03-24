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

class OrdersInfoGetTotalShippings
{
    /**
     * Returns total shippings
     *
     * @return array total shippings
     */
    public static function run()
    {
        $id_carriers = \ModelBrtConfig::getBrtCarriersId();
        $id_delivered = \ModelBrtConfig::getBrtOsDelivered();
        $id_state_skip = \ModelBrtConfig::getBrtOsSkip();
        $max_date = date('Y-m-d', strtotime('-30 days'));

        if (!$id_carriers) {
            return [
                'success' => false,
                'message' => 'Nessun carrier configurato',
            ];
        }

        if ($id_carriers) {
            if (!is_array($id_carriers)) {
                $id_carriers = json_decode($id_carriers, true);
            }

            if (!is_array($id_carriers)) {
                $id_carriers = [$id_carriers];
            }

            $id_carriers = implode(',', $id_carriers);
        }

        if ($id_delivered) {
            if (!is_array($id_delivered)) {
                $id_delivered = json_decode($id_delivered, true);
            }

            if (!is_array($id_delivered)) {
                $id_delivered = [$id_delivered];
            }

            $id_delivered = implode(',', $id_delivered);
        }

        $db = \Db::getInstance();

        // Tutti gli ordini senza tracking number
        $ocTable = _DB_PREFIX_ . 'order_carrier';
        $oTable = _DB_PREFIX_ . 'orders';
        $query = "
            SELECT 
                t1.id_order, 
                t1.tracking_number
            FROM 
                {$ocTable} t1
            INNER JOIN 
                {$oTable} o ON t1.id_order = o.id_order
            INNER JOIN 
                (SELECT 
                    id_order, 
                    MAX(date_add) AS last_date
                FROM 
                    {$ocTable}
                WHERE 
                    tracking_number IS NOT NULL AND tracking_number <> ''
                GROUP BY 
                    id_order) t2
            ON 
                t1.id_order = t2.id_order AND t1.date_add = t2.last_date
            WHERE 
                t1.tracking_number IS NULL OR t1.tracking_number = ''
        ";

        $startsFrom = (int) \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_START_FROM);
        if ($startsFrom) {
            $query .= "\n AND t1.id_order >= {$startsFrom}";
        } else {
            $query .= "\n AND t1.date_add >= \"{$max_date}\"";
        }

        if ($id_delivered) {
            $query .= "\n AND o.current_state NOT IN ({$id_delivered})";
        }

        if ($id_state_skip && is_array($id_state_skip)) {
            $query .= "\n AND o.current_state NOT IN (" . implode(',', $id_state_skip) . ')';
        }

        $noTracking = $db->executeS($query);
        if (!$noTracking) {
            $noTracking = [];
        }

        // Tutti gli ordini con tracking number
        $ocTable = _DB_PREFIX_ . 'order_carrier';
        $oTable = _DB_PREFIX_ . 'orders';
        $query = "
            SELECT 
                t1.id_order, 
                t1.tracking_number
            FROM 
                {$ocTable} t1
            INNER JOIN 
                {$oTable} o ON t1.id_order = o.id_order
            INNER JOIN 
                (SELECT 
                    id_order, 
                    MAX(date_add) AS last_date
                FROM 
                    {$ocTable}
                WHERE 
                    tracking_number IS NOT NULL AND tracking_number <> ''
                GROUP BY 
                    id_order) t2
            ON 
                t1.id_order = t2.id_order AND t1.date_add = t2.last_date
            WHERE 
                t1.tracking_number IS NOT NULL AND t1.tracking_number <> ''
        ";

        $startsFrom = (int) \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_START_FROM);
        if ($startsFrom) {
            $query .= "\n AND t1.id_order >= {$startsFrom}";
        } else {
            $query .= "\n AND t1.date_add >= \"{$max_date}\"";
        }

        if ($id_delivered) {
            $query .= "\n AND o.current_state NOT IN ({$id_delivered})";
        }

        if ($id_state_skip && is_array($id_state_skip)) {
            $query .= "\n AND o.current_state NOT IN (" . implode(',', $id_state_skip) . ')';
        }

        $tracking = $db->executeS($query);
        if (!$tracking) {
            $tracking = [];
        }

        return [
            'status' => 'success',
            'totalShippings' => count($noTracking) + count($tracking),
            'list' => [
                'getTracking' => $noTracking,
                'getShipment' => $tracking,
            ],
        ];
    }
}
