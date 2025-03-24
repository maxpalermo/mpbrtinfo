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

use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByIdCollo;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByRMA;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByRMN;

class OrdersInfoGetTrackingNumbers
{
    /**
     * Returns tracking numbers
     *
     * @return array tracking numbers
     */
    public static function run($list)
    {
        $processed = 0;
        $brt_customer_id = (int) \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER);

        $getTrackingBy = \ModelBrtConfig::getConfigValue(
            \ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE,
            'RMN'
        );

        $getTrackingWhere = \ModelBrtConfig::getConfigValue(
            \ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE,
            'ID'
        );

        switch ($getTrackingBy) {
            case 'RMN':
                $getTrackingBy = new GetIdSpedizioneByRMN();

                break;
            case 'RMA':
                $getTrackingBy = new GetIdSpedizioneByRMA();

                break;
            default:
                $getTrackingBy = new GetIdSpedizioneByIdCollo();

                break;
        }

        if (!$getTrackingWhere == 'ID') {
            $field = 'id';
        } elseif ($getTrackingWhere == 'REFERENCE') {
            $field = 'reference';
        } else {
            $field = 'id';
        }

        if ($list && is_array($list)) {
            foreach ($list as &$item) {
                $id_order = $item['id_order'];
                $tracking_number = $item['tracking_number'];

                $order = new \Order($id_order);
                if (!\Validate::isLoadedObject($order)) {
                    $item['status'] = 'error';
                    $item['message'] = 'Ordine non trovato';

                    continue;
                }

                $id_spedizione = $order->$field;

                if (!$tracking_number) {
                    $tracking = $getTrackingBy->getIdSpedizione($brt_customer_id, $id_spedizione);
                    if ($tracking) {
                        $item['status'] = 'success';
                        $item['esito'] = $tracking['esito'];
                        $item['versione'] = $tracking['versione'];
                        $item['tracking_number'] = $tracking['spedizione_id'];
                        $item['message'] = 'Tracking number ottenuto';
                        $processed++;
                    } else {
                        $item['status'] = 'error';
                        $item['message'] = 'Tracking number non ottenuto';
                    }
                }
            }
        }

        return [
            'status' => 'success',
            'processed' => $processed,
            'total' => count($list),
            'list' => $list,
        ];
    }
}
