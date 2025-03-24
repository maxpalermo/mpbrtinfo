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

use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\WSDL\GetTrackingByBrtShipmentId;

class OrdersInfoGetTrackingsByShipmentId
{
    /**
     * Returns tracking info
     *
     * @return array tracking info
     */
    public static function run($params)
    {
        $processed = 0;
        $list = $params['list'] ?? [];
        $lingua_iso639_alpha2 = isset($params['lingua']) ? $params['lingua'] : '';

        if ($list && is_array($list)) {
            foreach ($list as &$item) {
                $id_order = $item['id_order'];
                $shipping_year = (new GetOrderShippingDate($id_order))->getShippingYear();
                // Mi assicuro che il tracking non sia un ID COLLO
                $tracking_number = ConvertIdColloToTracking::convert($item['tracking_number']);

                $order = new \Order($id_order);
                if (!\Validate::isLoadedObject($order)) {
                    $item['status'] = 'error';
                    $item['message'] = 'Ordine non trovato';

                    continue;
                }

                if ($tracking_number) {
                    // Crea un'istanza della classe
                    $client = new GetTrackingByBrtShipmentId();

                    // Esegui la chiamata al servizio
                    $response = $client->getTracking($tracking_number, $id_order, $lingua_iso639_alpha2, $shipping_year);

                    // Controlla che l'esito sia andato a buon fine
                    if ($response['ESITO'] < 0) {
                        $item['status'] = 'error';
                        $item['message'] = 'Informazione spedizione non presente';

                        continue;
                    }

                    // Controlla l'esito, aggiorna lo stato, invia l'email
                    $parsedData = OrdersInfoParseShippingData::run(
                        [
                            'id_order' => $id_order,
                            'tracking_number' => $tracking_number,
                            'data' => $response,
                        ]
                    );

                    if ($parsedData['success'] == 'true') {
                        $item['status'] = 'success';
                        $item['message'] = 'Informazione spedizione trovata';
                        $item['parsedData'] = $parsedData;
                        $processed++;
                    } else {
                        $item['status'] = 'error';
                        $item['message'] = 'Informazione spedizione non presente';
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
