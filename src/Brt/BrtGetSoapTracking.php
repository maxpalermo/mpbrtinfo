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

namespace MpSoft\MpBrtInfo\Brt;

if (!defined('_PS_VERSION_')) {
    exit;
}
use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\Soap\BrtSoapAlerts;
use MpSoft\MpBrtInfo\Soap\BrtSoapShipmentId;
use MpSoft\MpBrtInfo\Soap\BrtSoapShipmentInfo;

class BrtGetSoapTracking
{
    /** @var \Module */
    protected $module;
    protected $id_lang;
    protected $name;

    public function __construct($module)
    {
        $this->module = $module;
        $this->id_lang = (int) \Context::getContext()->language->id;
        $this->name = 'BrtGetSoapTracking';
    }

    public function get()
    {
        $db = \Db::getInstance();
        $alerts = BrtSoapAlerts::getInstance();
        $check_events = \ModelBrtConfig::getCheckEvents();
        $os_delivered = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::CONFIG_EVENT_DELIVERED, true);
        $brt_carriers = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::CONFIG_BRT_CARRIERS, true);
        $trackings = [];

        $alerts->clearAll();

        foreach ($brt_carriers as &$brt_carrier) {
            $brt_carrier = "'" . pSQL($brt_carrier) . "'";
        }

        $sql = new \DbQuery();
        $sql->select('id_carrier')
            ->from('carrier')
            ->where('name in (' . implode(',', $brt_carriers) . ')');
        $carriers = $db->executeS($sql);
        $id_carriers = [];
        if ($carriers) {
            foreach ($carriers as $carrier) {
                $id_carriers[] = (int) $carrier['id_carrier'];
            }
        }

        $sql = new \DbQuery();
        $sql->select('a.id_order, a.id_carrier, b.tracking_number')
            ->from('orders', 'a')
            ->innerJoin('order_carrier', 'b', 'a.id_order=b.id_order and a.id_carrier in (' . implode(',', $id_carriers) . ')')
            ->where('a.current_state not in (' . implode(',', $os_delivered) . ')')
            ->where('a.current_state in (' . implode(',', $check_events) . ')')
            ->orderBy('a.id_order');
        // ->where('b.tracking_number IS NOT NULL OR b.tracking_number = \'\'');

        $rows = $db->executeS($sql);
        if ($rows) {
            foreach ($rows as $row) {
                $year = (new GetOrderShippingDate($row['id_order']))->getShippingYear();
                if ($row['tracking_number']) {
                    $shipment_id = $row['tracking_number'];
                } else {
                    $tracking = (new BrtSoapShipmentId(null, null, $row['id_order']))->getShipmentId();
                    if ($tracking && isset($tracking['ESITO']) && $tracking['ESITO'] > -1) {
                        $shipment_id = $tracking['SPEDIZIONE_ID'];
                    } else {
                        continue;
                    }
                }

                $evento = (new BrtSoapShipmentInfo($shipment_id, $year))->getLastEvento();
                if (isset($evento['error']) && $evento['error'] == true) {
                    $os = false;
                } else {
                    $os = \ModelBrtEvento::getOrderStateByIdEvento($evento['EVENTO']['ID']);
                }

                $trackings[] = [
                    'id_order' => $row['id_order'],
                    'id_carrier' => $row['id_carrier'],
                    'id_order_carrier' => $this->getIdOrderCarrier($row['id_order'], $row['id_carrier']),
                    'tracking' => $shipment_id,
                    'event' => $evento,
                    'id_order_state' => $os,
                ];

                unset($tracking);
            }
        }

        $counter = count($rows);
        $processed_tracking = 0;
        $processed_os = 0;

        if ($trackings) {
            foreach ($trackings as $tracking) {
                if (isset($tracking['event']['error']) && $tracking['event']['error'] == true) {
                    continue;
                }
                $orderCarrier = new \OrderCarrier($tracking['id_order_carrier']);
                if ($orderCarrier->tracking_number != $tracking['tracking']) {
                    $orderCarrier->tracking_number = pSQL($tracking['tracking']);
                    $res = $orderCarrier->update();
                    if ($res) {
                        $alerts->addConfirmationMessage(
                            sprintf(
                                $this->module->l('Order %d updated with tracking %s', $this->name),
                                $tracking['id_order'],
                                '<strong>' . $tracking['tracking'] . '</strong><br>'
                            )
                        );
                        ++$processed_tracking;
                    }
                }

                if ($tracking['id_order_state'] && (new \Order($tracking['id_order']))->current_state != $tracking['id_order_state']) {
                    (new \OrderHistory())->changeIdOrderState($tracking['id_order_state'], $tracking['id_order']);
                    $os = new \OrderState($tracking['id_order_state'], $this->id_lang);
                    $alerts->addConfirmationMessage(
                        sprintf(
                            $this->module->l('Order %d changed state in %s', $this->name),
                            $tracking['id_order'],
                            '<strong>' . $os->name . '</strong><br>'
                        )
                    );
                    ++$processed_os;
                }
            }
        }

        if ($counter) {
            $alerts->addConfirmationMessage(
                sprintf(
                    $this->module->l('Checked %s orders. Tracking found: %s. Order state changed: %s', $this->name),
                    "<strong>$counter</strong>",
                    "<strong>$processed_tracking</strong>",
                    "<strong>$processed_os</strong><br>"
                )
            );
        } else {
            $alerts->addWarningMessage($this->module->l('No orders processed.', $this->name));
        }

        return $trackings;
    }

    public function getIdOrderCarrier($id_order, $id_carrier)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_order_carrier')
            ->from('order_carrier')
            ->where('id_order=' . (int) $id_order)
            ->where('id_carrier=' . (int) $id_carrier)
            ->orderBy('date_add DESC');

        return (int) $db->getValue($sql);
    }
}
