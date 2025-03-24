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

class OrderEventsCount
{
    /**
     * Summary of run
     *
     * @param int $orderId
     * @param int $eventId
     * @param string $date
     *
     * @return bool|string|null count of order events or false
     */
    public static function get($orderId, $eventId = 0, $date = '')
    {
        $primary = \ModelBrtHistory::$definition['primary'];
        $table = \ModelBrtHistory::$definition['table'];
        $db = \Db::getInstance();
        $sql = new \DbQuery();

        $orderId = (int) $orderId;
        $eventId = (int) $eventId;
        $date = pSQL($date);

        if (!$orderId) {
            return false;
        }

        $sql->select("count({$primary})")
            ->from($table)
            ->where('id_order = ' . (int) $orderId);

        if ($eventId) {
            $sql->where('event_id = ' . (int) $eventId);
        }

        if ($date && \Validate::isDate($date)) {
            $sql->where('event_date = \'' . $date . '\'');
        }

        $id = $db->getValue($sql);

        return $id;
    }
}
