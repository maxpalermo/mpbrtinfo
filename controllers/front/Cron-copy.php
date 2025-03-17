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
if (!defined('_PS_VERSION_')) {
    exit;
}

use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\Response\Response;
use MpSoft\MpBrtInfo\Soap\TrackingInfo;
use MpSoft\MpBrtInfo\Soap\TrackingNumber;

class MpBrtInfoCronModuleFrontController extends ModuleFrontController
{
    protected $post_json;

    public function displayGetShippingsInfo()
    {
        $nl = php_sapi_name() === 'cli' ? "\n" : "<br>";
        $total_shippings = $this->ajaxFetchTotalShippings(null);
        echo "Trovati " . count($total_shippings['list']['getTracking']) . " spedizioni senza tracking number" . $nl;
        echo "Trovati " . count($total_shippings['list']['getShipment']) . " spedizioni da controllare" . $nl;
        echo $nl . $nl;
        echo "Ricerca tracking" . $nl;
        echo "================" . $nl;
        foreach ($total_shippings['list']['getTracking'] as $tracking) {
            $tracking_number = TrackingNumber::get($tracking['id_order']);
            if ($tracking_number) {
                echo "ID: " . $tracking['id_order'] . " - Tracking number: " . $tracking_number . $nl;
            } else {
                echo "ID: " . $tracking['id_order'] . " - Non trovato" . $nl;
            }
        }

        echo $nl . $nl;
        
        echo "Ricerca info spedizioni" . $nl;
        echo "=======================" . $nl;
        foreach ($total_shippings['list']['getShipment'] as $shipment) {
            $year = (new GetOrderShippingDate($shipment['id_order']))->getShippingYear();
            $info = TrackingInfo::get($shipment['id_order'], $shipment['tracking_number'], $year);
            
            if ($info && $info->getEsito() == 0) {
                $isDelivered = $info->isDelivered();

                if ($isDelivered) {
                    echo "ID: " . $shipment['id_order'] . " - Spedizione consegnata" . $nl;
                } else {
                    echo "ID: " . $shipment['id_order'] . " - Spedizione non consegnata" . $nl;
                }
            }
        }
        exit;
    }

    public function display()
    {
        if (Tools::isSubmit('action') && Tools::getValue('action') === 'getShippingsInfo') {
            $this->displayGetShippingsInfo();
        }

        $this->setTemplate('module:mpbrtinfo/views/templates/front/cron/displayGetShippingsInfo');

        return true;
    }

    public function ajaxFetchTotalShippings($post_json)
    {
        $id_carriers = ModelBrtConfig::getCarriers();
        $id_delivered = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED, 0);
        $id_state_skip = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_OS_SKIP, []);
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

        $db = Db::getInstance();

        // Tutti gli ordini senza tracking number
        $sql = new DbQuery();
        $sql->select('oc.id_order, oc.tracking_number')
            ->from('order_carrier', 'oc')
            ->innerJoin('orders', 'o', 'oc.id_order = o.id_order')
            ->where('o.id_carrier IN (' . $id_carriers . ')')
            ->where('o.date_add >= "' . $max_date . '"')
            ->groupBy('oc.id_order')
            ->having('MAX(oc.tracking_number) IS NULL OR MAX(oc.tracking_number) = ""')
            ->orderBy('o.id_order DESC');

        if ($id_delivered) {
            $sql->where('o.current_state NOT IN (' . $id_delivered . ')');
        }

        if ($id_state_skip && is_array($id_state_skip)) {
            $sql->where('o.current_state NOT IN (' . implode(',', $id_state_skip) . ')');
        }

        $noTracking = $db->executeS($sql);
        if (!$noTracking) {
            $noTracking = [];
        }

        // Tutti gli ordini con tracking number
        $sql = new DbQuery();
        $sql->select('oc.id_order, oc.tracking_number')
            ->from('order_carrier', 'oc')
            ->innerJoin('orders', 'o', 'oc.id_order = o.id_order')
            ->where('o.id_carrier IN (' . $id_carriers . ')')
            ->where('o.date_add >= "' . $max_date . '"')
            ->groupBy('oc.id_order')
            ->having('MAX(oc.tracking_number) IS NOT NULL AND MAX(oc.tracking_number) != ""')
            ->orderBy('o.id_order DESC');

        if ($id_delivered) {
            $sql->where('o.current_state NOT IN (' . $id_delivered . ')');
        }

        if ($id_state_skip && is_array($id_state_skip)) {
            $sql->where('o.current_state NOT IN (' . implode(',', $id_state_skip) . ')');
        }

        $tracking = $db->executeS($sql);
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
