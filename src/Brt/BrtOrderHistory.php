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

use MpSoft\MpBrtInfo\Soap\BrtSoapShipmentInfo;

class BrtOrderHistory
{
    protected $id_order_history;
    protected $id_order;
    protected $id_order_state;

    public function __construct($id_order_history, $id_order, $id_order_state)
    {
        $this->id_order_history = (int) $id_order_history;
        $this->id_order = (int) $id_order;
        $this->id_order_state = (int) $id_order_state;
    }

    public function updateBrtOrderHistory()
    {
        return true;

        $order = new \Order($this->id_order);
        $order_state = new \OrderState($this->id_order_state);
        $order_history = new \OrderHistory($this->id_order_history);

        if (!\Validate::isLoadedObject($order)) {
            \Context::getContext()->controller->errors[] = 'ID ORDER NOT VALID ' . $this->id_order;

            return false;
        }

        if (!\Validate::isLoadedObject($order_state)) {
            \Context::getContext()->controller->errors[] = 'ID ORDER STATE NOT VALID ' . $this->id_order_state;

            return false;
        }

        if (!\Validate::isLoadedObject($order_history)) {
            \Context::getContext()->controller->errors[] = 'ID ORDER HISTORY NOT VALID ' . $this->id_order_history;

            return false;
        }

        $tracking = BrtSoapShipmentInfo::getTrackingNumber($order->id, $order->id_carrier);

        if ($this->isSentOs()) {
            // CREA IL RECORD
            $model = new \ModelBrtDelivered($this->id_order);
            if (!\Validate::isLoadedObject($model)) {
                $model->force_id = true;
                $model->id = $order_history->id_order;
                $model->tracking_number = $tracking;
                $model->date_shipped = $order_history->date_add;
                $model->days = 0;

                if ($model->add()) {
                    return 'SHIPPED';
                }
            }
        } elseif ($this->isDeliveredOs()) {
            // AGGIORNA IL RECORD A CONSEGNATO
            $model = new \ModelBrtDelivered($this->id_order);
            if (\Validate::isLoadedObject($model)) {
                $model->tracking_number = $tracking;
                $model->date_delivered = $order_history->date_add;
                $model->days = MpBrtDays::countDays($model->date_shipped, $model->date_delivered);

                if ($model->update()) {
                    return 'DELIVERED';
                }
            }
        }

        return false;
    }

    public function isTransitOs()
    {
        $array = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT);

        return in_array($this->id_order_state, $array);
    }

    public function isDeliveredOs()
    {
        $array = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);

        return in_array($this->id_order_state, $array);
    }

    public function isErrorOs()
    {
        $array = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR);

        return in_array($this->id_order_state, $array);
    }

    public function isFermoPointOs()
    {
        $array = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT);

        return in_array($this->id_order_state, $array);
    }

    public function isRefusedOs()
    {
        $array = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED);

        return in_array($this->id_order_state, $array);
    }

    public function isWaitingOs()
    {
        $array = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING);

        return in_array($this->id_order_state, $array);
    }

    public function isSentOs()
    {
        $array = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);

        return in_array($this->id_order_state, $array);
    }
}