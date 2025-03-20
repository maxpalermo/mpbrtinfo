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

class GetTrackingFromOrderCarrier
{
    /**
     * Ottiene il tracking number o l'id collo da OrderCarrier
     * 
     * @param int $carrierId
     * @param int $orderId
     * @param bool $convert se attivo restituisce sempre il tracking number convertito
     *
     * @return string|false Restituisce l'id collo o lo converte in tracking number
     */
    public static function get($carrierId, $orderId, $convert = true)
    {
        $db = \Db::getInstance(_PS_USE_SQL_SLAVE_);
        $sql = new \DbQuery();
        $sql->select('id_order_carrier')
            ->from('order_carrier')
            ->where('id_order = ' . (int) $orderId)
            ->where('id_carrier = ' . (int) $carrierId);
        $id_order_carrier = (int) $db->getValue($sql);

        $orderCarrier = new \OrderCarrier($id_order_carrier);
        if (!\Validate::isLoadedObject($orderCarrier)) {
            \PrestaShopLogger::addLog('OrderCarrier not found', 1, 0, 'OrderCarrier', 0);

            return false;
        }

        return $convert ? ConvertIdColloToTracking::convert($orderCarrier->tracking_number) : $orderCarrier->tracking_number;
    }
}
