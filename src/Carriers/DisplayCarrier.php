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

namespace MpSoft\MpBrtInfo\Carriers;

if (!defined('_PS_VERSION_')) {
    exit;
}

use MpSoft\MpBrtInfo\Helpers\SmartyTpl;

class DisplayCarrier
{
    /** @var \Module */
    protected $module;
    /** @var \ModuleAdminController */
    protected $controller;
    /** @var \Context */
    protected $context;
    /** @var string */
    protected $name;
    /** @var SmartyTpl */
    protected $tpl;
    protected $id_lang;
    /** @var array */
    protected $carriers;
    /** @var array */
    protected $carriers_id;
    protected $tracking;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = \Context::getContext();
        $this->controller = $this->context->controller;
        $this->tpl = new SmartyTpl();
        $this->id_lang = (int) \Context::getContext()->language->id;
        $this->carriers_brt = $this->getIdCarrierByName(\ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS));
    }

    public function display($id_order)
    {
        $order = new \Order($id_order, $this->id_lang);
        if (!\Validate::isLoadedObject($order)) {
            return $this->displayError();
        }
        $id_carrier = $order->id_carrier;
        $carrier = new \Carrier($id_carrier, $this->id_lang);
        if (!\Validate::isLoadedObject($carrier)) {
            return $this->displayError();
        }
        $carrier_name = $carrier->name;
        $this->tracking = $this->getTracking($id_order, $id_carrier);

        $event_sent = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT, true);
        $event_delivered = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED, true);
        $event_error = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR, true);
        $event_fermopoint = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT, true);
        $event_refused = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED, true);
        $event_transit = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT, true);
        $event_waiting = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING, true);

        $current_os = (int) $order->current_state;
        $displayIcon = \ModelBrtConfig::getIcon('');

        if (!$this->isBrtCarrier($id_carrier)) {
            return $this->displayCarrierIcon($id_order, $id_carrier);
        }

        if (in_array($current_os, $event_delivered)) {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
        } elseif (in_array($current_os, $event_error)) {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR);
        } elseif (in_array($current_os, $event_fermopoint)) {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT);
        } elseif (in_array($current_os, $event_refused)) {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED);
        } elseif (in_array($current_os, $event_transit)) {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT);
        } elseif (in_array($current_os, $event_waiting)) {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING);
        } elseif (in_array($current_os, $event_sent)) {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);
        }

        $params = [
            'carrier' => [
                'icon' => $displayIcon,
                'id_order' => $id_order,
                'id_carrier' => $id_carrier,
                'tracking' => '', // $tracking,
                'name' => $carrier_name,
                'carrier_url' => $carrier->url,
            ],
        ];

        return $this->tpl->renderTplAdmin('brtIcon/brt_carrier', $params);
    }

    protected function displayError()
    {
        return "<i class='icon-warning text-danger'></i>";
    }

    public function nameToArray($carrier_name)
    {
        if (!$carrier_name) {
            return false;
        }
        $carriers = [];
        if (is_array($carrier_name)) {
            foreach ($carrier_name as $carrier) {
                $carriers[] = '\'' . pSQL($carrier) . '\'';
            }
        } else {
            $carriers = [$carrier_name];
        }

        return $carriers;
    }

    public function getIdCarrierByName($carrier_name = '')
    {
        $carriers = $this->nameToArray($carrier_name);
        if (!$carriers) {
            return [];
        }
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_carrier')
            ->from('carrier')
            ->where('name in (' . implode(',', $carriers) . ')');

        $sql = $sql->build();
        $rows = $db->executeS($sql);

        $out = [];
        if ($rows) {
            foreach ($rows as $row) {
                $out[] = (int) $row['id_carrier'];
            }
        }

        return $out;
    }

    public function getIconPathByIdEvent($id_evento)
    {
        $event = \ModelBrtEvento::getById($id_evento);
        if (\Validate::isLoadedObject($event)) {
            if ($event->isSent()) {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);
            } elseif ($event->isWaiting()) {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING);
            } elseif ($event->isError()) {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR);
            } elseif ($event->isRefused()) {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED);
            } elseif ($event->isTransit()) {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT);
            } elseif ($event->isDelivered()) {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
            } elseif ($event->isFermopoint()) {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT);
            } else {
                $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_UNKNOWN);
            }
        } else {
            $displayIcon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_UNKNOWN);
        }

        return $displayIcon;
    }

    public function getTracking($id_order, $id_carrier)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();

        $sql->select('tracking_number')
            ->from('order_carrier')
            ->where('id_order=' . (int) $id_order)
            ->where('id_carrier=' . (int) $id_carrier);

        return $db->getValue($sql);
    }

    public function isBrtCarrier($id_carrier)
    {
        $carriers = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS);
        $carrier = new \Carrier($id_carrier, $this->id_lang);

        return in_array($carrier->name, $carriers);
    }

    public function displayCarrierIcon($id_order, $id_carrier)
    {
        $carrier = new \Carrier($id_carrier);
        if (\Validate::isLoadedObject($carrier)) {
            $icon = $this->context->shop->getBaseURI() . 'img/s/' . $id_carrier . '.jpg';
            $params = [
                'carrier' => [
                    'icon' => $icon,
                    'id_order' => false,
                    'id_carrier' => $carrier->id,
                    'tracking' => $this->getTracking($id_order, $id_carrier),
                    'name' => $carrier->name,
                    'url' => $this->getCarrierLink($id_order),
                ],
            ];
        } else {
            $icon = \ModelBrtConfig::getIcon(\ModelBrtConfig::MP_BRT_INFO_EVENT_UNKNOWN);
            $params = [
                'carrier' => [
                    'icon' => $icon,
                    'id_order' => false,
                    'id_carrier' => false,
                    'tracking' => false,
                    'name' => $this->module->l('Carrier Unavailable', $this->name),
                    'url' => 'javascript:void(0);',
                ],
            ];
        }

        return $this->tpl->renderTplAdmin('brtIcon/carrier', $params);
    }

    private function getCarrierLink($id_order)
    {
        $order = new \Order($id_order);
        $carrier = new \Carrier($order->id_carrier);
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('tracking_number')
            ->from('order_carrier')
            ->where('id_order=' . (int) $id_order)
            ->where('id_carrier=' . (int) $order->id_carrier)
            ->orderBy('date_add DESC');
        $tracking_number = $db->getValue($sql);
        if ($tracking_number) {
            $link = $carrier->url;
            $link = str_replace('@', $tracking_number, $link);

            return $link;
        }

        return false;
    }
}