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

if (!defined('_PS_VERSION_')) {
    exit;
}

use MpSoft\MpBrtInfo\Carriers\DisplayCarrier;
use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\Soap\BrtSoapEventi;
use MpSoft\MpBrtInfo\Soap\BrtSoapShipmentId;
use MpSoft\MpBrtInfo\Soap\BrtSoapShipmentInfo;

class BrtInfoHelper
{
    /** @var int */
    protected $id_order;
    /** @var \Order */
    protected $order;
    /** @var int */
    protected $id_carrier;
    protected $id_lang;
    /** @var BrtSoapEventi */
    protected $eventi;
    /** @var array */
    protected $tracking_states;
    /** @var SmartyTpl */
    protected $tpl;
    protected $module;
    protected $name;
    protected $shipment_id = '';
    protected $year = '';
    protected $errors;
    protected $follow_up;
    protected $tracking;

    public function __construct($id_order, $id_carrier, $shipment_id = '', $year = '')
    {
        $this->name = 'BrtInfoHelper';
        $this->module = \Module::getInstanceByName('mpbrtinfo');
        $this->id_lang = (int) \Context::getContext()->language->id;
        $this->id_order = (int) $id_order;
        $this->id_carrier = (int) $id_carrier;
        $this->order = new \Order($id_order, $this->id_lang);
        $this->shipment_id = $shipment_id;
        if (!$year) {
            $date_order = strtotime($this->order->date_add);
            $this->year = date('Y', $date_order);
        } else {
            $this->year = $year;
        }
        $this->id_carrier = (int) $this->order->id_carrier;
        $this->eventi = new BrtSoapEventi();
        $this->tracking_states = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_OS_CHECK_FOR_TRACKING);
        $this->tpl = new SmartyTpl();
        $this->errors = [];
        $this->tracking = $this->getOrderCarrierTrackingNumber($id_order, $id_carrier);
    }

    public function getOrderCarrierTrackingNumber($id_order, $id_carrier)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();

        $sql->select('tracking_number')
            ->from('order_carrier')
            ->where('id_order=' . (int) $id_order)
            ->where('id_carrier=' . (int) $id_carrier);

        return $db->getValue($sql);
    }

    public function getSoapTrackingNumber()
    {
        $shipment = new BrtSoapShipmentId(null, null, $this->shipment_id);
        $shipment->createRequest(null, $this->shipment_id);
        $shipmentId = $shipment->getShipmentId();
        if (isset($shipmentId['ESITO']) && $shipmentId['ESITO'] == 0) {
            $tracking = $shipmentId['SPEDIZIONE_ID'];
            $res = $this->setTrackingNumber($this->id_order, $this->id_carrier, $tracking);
            if ($res) {
                $this->changeOrderState(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);
            }
            if ($res) {
                $data = [
                    'message' => sprintf(
                        $this->module->l('Tracking %s inserito. Ordine %d'),
                        $tracking,
                        $this->id_order
                    ),
                ];
                $tpl_success = $this->tpl->renderTplAdmin('messages/esito_confirm', $data);

                return [
                    'error' => false,
                    'dialog' => $tpl_success,
                ];
            }
        } else {
            $error = \ModelBrtEsito::getByIdEsito($shipmentId['ESITO']);
            if (\Validate::isLoadedObject($error)) {
                $data = [
                    'id_esito' => $error->id_esito,
                    'testo1' => $error->testo1,
                    'testo2' => $error->testo2,
                ];
                $tpl_warning = $this->tpl->renderTplAdmin('messages/esito_error', $data);

                return [
                    'error' => true,
                    'dialog' => $tpl_warning,
                ];
            }
        }
    }

    public function setTrackingNumber($id_order, $id_carrier, $tracking)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_order_carrier')
            ->from('order_carrier')
            ->where('id_order=' . (int) $id_order)
            ->where('id_carrier=' . (int) $id_carrier)
            ->orderBy('date_add DESC');
        $id_order_carrier = (int) $db->getValue($sql);
        $order_carrier = new \OrderCarrier($id_order_carrier, $this->id_lang);
        if (!\Validate::isLoadedObject($order_carrier)) {
            return false;
        }

        $order_carrier->tracking_number = $tracking;

        return $order_carrier->update();
    }

    public function changeOrderState($event)
    {
        switch ($event) {
            case \ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR:
                $id_order_state = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR);

                break;
            case \ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED:
                $id_order_state = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED);

                break;
            case \ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING:
                $id_order_state = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING);

                break;
            case \ModelBrtConfig::MP_BRT_INFO_EVENT_SENT:
                $id_order_state = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);

                break;
            case \ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT:
                $id_order_state = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT);

                break;
            case \ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT:
                $id_order_state = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT);

                break;
            case \ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED:
                $id_order_state = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);

                break;
            default:
                return false;
        }

        $order_state = new \OrderState($id_order_state);

        if (!\Validate::isLoadedObject($order_state)) {
            $this->errors[] = $this->module->l(
                sprintf('Order status #%d%# cannot be loaded', $id_order_state),
                $this->name
            );

            return false;
        } else {
            $order = new \Order((int) $this->id_order);
            if (!\Validate::isLoadedObject($order)) {
                $this->errors[] = $this->module->l(
                    sprintf('Order #%d%# cannot be loaded', $this->id_order),
                    $this->name
                );

                return false;
            } else {
                $current_order_state = $order->getCurrentOrderState();
                if ($current_order_state->id == $order_state->id) {
                    $this->errors[] = $this->module->l(
                        sprintf('Order #%d has already been assigned this status.', $this->id_order),
                        $this->name
                    );

                    return false;
                } else {
                    $history = new \OrderHistory();
                    $history->id_order = $order->id;
                    $history->id_employee = (int) \Context::getContext()->employee->id;

                    $use_existing_payment = !$order->hasInvoice();
                    $history->changeIdOrderState((int) $order_state->id, $this->id_order, $use_existing_payment);

                    $carrier = new \Carrier($order->id_carrier, $order->id_lang);
                    if ($history->id_order_state == \Configuration::get('PS_OS_SHIPPING') && $this->tracking) {
                        $this->follow_up = ['{followup}' => str_replace('@', $this->tracking, $carrier->url)];
                    }

                    return true;
                }
            }
        }
    }

    public function getFollowUp()
    {
        return $this->follow_up;
    }

    public function getOrderInfo()
    {
        if ($this->shipment_id) {
            $this->tracking = $this->shipment_id;
        }
        if ($this->year) {
            $year = $this->year;
        } else {
            $year = (new GetOrderShippingDate($this->id_order))->getShippingYear();
        }

        if (!$this->tracking) {
            $brtSoapShipmentId = new BrtSoapShipmentId(null, null, $this->id_order);
            $shipmentId = $brtSoapShipmentId->getShipmentId();

            if (isset($shipmentId['ESITO']) && $shipmentId['ESITO'] == 0) {
                $this->tracking = $shipmentId['SPEDIZIONE_ID'];
            } else {
                $error = \ModelBrtEsito::getByIdEsito($shipmentId['ESITO']);
                if (\Validate::isLoadedObject($error)) {
                    $data = [
                        'id_esito' => $error->id_esito,
                        'testo1' => $error->testo1,
                        'testo2' => $error->testo2,
                    ];
                    $tpl_warning = $this->tpl->renderTplAdmin('messages/esito_error', $data);

                    return [
                        'error' => true,
                        'dialog' => $tpl_warning,
                    ];
                }
            }
        }

        if (!$this->tracking) {
            $data = [
                'id_esito' => -999,
                'testo1' => $this->module->l('Tracking non trovato', $this->name),
                'testo2' => '',
            ];
            $tpl_warning = $this->tpl->renderTplAdmin('messages/esito_error', $data);

            return [
                'error' => true,
                'dialog' => $tpl_warning,
            ];
        }

        $shipmentInfo = new BrtSoapShipmentInfo($this->tracking, $year);
        $eventi = $shipmentInfo->getShipmentInfoBySoap($this->tracking, $year);

        if (isset($eventi['ESITO']) && $eventi['ESITO'] == 0) {
            foreach ($eventi['LISTA_EVENTI'] as &$ev) {
                $id_evento = $ev['EVENTO']['ID'];
                if ($ev['EVENTO']['ID']) {
                    $icon = (new DisplayCarrier($this->module))->getIconPathByIdEvent($id_evento);
                    $ev['EVENTO']['ICON'] = $icon;
                }
            }
            $data = [
                'eventi' => $eventi,
            ];
            $tpl_eventi = $this->tpl->renderTplAdmin('messages/eventi', $data);

            return [
                'error' => false,
                'dialog' => $tpl_eventi,
            ];
        } else {
            $error = \ModelBrtEsito::getByIdEsito($eventi['ESITO']);
            if (\Validate::isLoadedObject($error)) {
                $data = [
                    'id_esito' => $error->id_esito,
                    'testo1' => $error->testo1,
                    'testo2' => $error->testo2,
                ];
                $tpl_warning = $this->tpl->renderTplAdmin('messages/esito_error', $data);

                return [
                    'error' => true,
                    'dialog' => $tpl_warning,
                ];
            }
        }
    }
}
