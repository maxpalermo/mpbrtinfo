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

class OrderEventPrestashopOS
{
    /**
     * Returns id_order_state of the order event
     *
     * @param int $eventId
     *
     * @return int|null id_order_state of the order event or null
     */
    public static function get($eventId)
    {
        $table = \ModelBrtEvento::$definition['table'];
        $db = \Db::getInstance();
        $sql = new \DbQuery();

        $eventId = pSQL($eventId);

        if (!$eventId) {
            return null;
        }

        $sql->select('id_order_state')
            ->from($table)
            ->where("id_evento = '{$eventId}'");

        $idOrderState = (int) $db->getValue($sql);

        return $idOrderState;
    }
}
