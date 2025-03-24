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

use MpSoft\MpBrtInfo\Bolla\BrtParseInfo;
use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;

class OrdersInfoParseShippingData
{
    /**
     * Returns tracking info
     *
     * @return array tracking info
     */
    public static function run($params)
    {
        $data = $params['data'];
        $id_order = $params['id_order'];
        $tracking_number = $params['tracking_number'];
        $year_shipped = (new GetOrderShippingDate($id_order))->getShippingYear();
        $shipment_data = BrtParseInfo::parseTrackingInfo($data, \ModelBrtConfig::getEsiti(), $id_order);
        $bolla = OrdersInfoPrepareShipmentData::run($shipment_data);
        OrdersInfoPrepareHistory::run($id_order, $year_shipped, $shipment_data);

        $events = [];
        if ($bolla['eventi']) {
            $eventi = $bolla['eventi'];
            foreach ($eventi as $evento) {
                if ($evento->isDelivered()) {
                    $bolla['days'] = OrdersInfoCountDays::run($id_order);
                }
                $events[] = [
                    'color' => $evento->getRow()['event_color'],
                    'icon' => $evento->getRow()['event_icon'],
                    'id' => $evento->getId(),
                    'data' => $evento->getData(),
                    'ora' => $evento->getOra(),
                    'descrizione' => $evento->getDescrizione(),
                    'filiale' => $evento->getFiliale(),
                    'label' => '(' . $evento->getRow()['event_id'] . ') ' . $evento->getRow()['event_name'],
                ];
            }
        }
        // Prepara la risposta
        $response = [
            'success' => true,
            'data' => [
                'id_order' => $id_order,
                'tracking_number' => $tracking_number,
                'data_spedizione' => $bolla['data_spedizione'] ?? date('d/m/Y'),
                'porto' => $bolla['porto'] ?? 'Franco',
                'servizio' => $bolla['servizio'] ?? 'Standard',
                'colli' => $bolla['colli'] ?? 1,
                'peso' => $bolla['peso'] ?? '0 Kg',
                'natura' => $bolla['natura'] ?? 'Merce',
                'stato_attuale' => OrdersInfoGetCurrentOrderState::run($id_order),
                'storico' => $events,
                'days' => $bolla['days'] ?? '--',
            ],
        ];

        $tpl = (new OrdersInfoTemplate())->createTemplate('FetchOrdersHandler/ParseShippingInfo.tpl', $response['data']);

        return ['success' => true, 'html' => $tpl];
    }
}
