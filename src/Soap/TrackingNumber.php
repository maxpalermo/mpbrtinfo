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

namespace MpSoft\MpBrtInfo\Soap;

use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;

class TrackingNumber
{
    public static function get($id_order)
    {
        $config_get_by = \ModelBrtConfig::getTrackingBy();
        $config_get_where = \ModelBrtConfig::getSearchWhere();
        $config_shippedStates = \ModelBrtConfig::getShippedStates();
        $config_id_customer = \ModelBrtConfig::getCustomerId();

        $order = new \Order($id_order);
        $year = (new GetOrderShippingDate($id_order))->getShippingYear();
        if (!\Validate::isLoadedObject($order)) {
            return false;
        }

        if ($config_get_where == \ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE_ID) {
            $rmn = $order->id;
            $rma = $order->id;
        } else {
            $rmn = $order->reference;
            $rma = $order->reference;
        }

        if ($config_get_by == \ModelBrtConfig::MP_BRT_INFO_SEARCH_BY_RMN) {
            $tracking = self::getTrackingByRMN($config_id_customer, $rmn, $year);
        } else {
            $tracking = self::getTrackingByRMA($config_id_customer, $rma, $year);
        }

        return $tracking;
    }

    public static function getTrackingByRMN($brt_customer_id, $rmn, $year)
    {
        $tracking = (new GetIdSpedizioneByRMN($rmn, $brt_customer_id, $year))->getIdSpedizione();

        return $tracking;
    }

    public static function getTrackingByRMA($brt_customer_id, $rma, $year)
    {
        $tracking = (new GetIdSpedizioneByRMA($rma, $brt_customer_id, $year))->getIdSpedizione();

        return $tracking;
    }
}
