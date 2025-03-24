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

use MpSoft\MpBrtInfo\Mail\Mailer;

class CarrierAttributes
{
    protected $module;
    protected $context;
    protected $id_lang;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('mpbrtinfo');
        $this->context = \Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
    }

    /**
     * Restituisce il link canonico di Bartolini al tracking number
     *
     * @param int $id_carrier
     * @param int $id_collo
     *
     * @return string
     */
    public function getCarrierLink($id_carrier, $id_collo)
    {
        // @deprecated version
        if (1 == 0) {
            $carrier = new \Carrier($id_carrier);
            if (!\Validate::isLoadedObject($carrier)) {
                return 'javascript:void(0);';
            }

            return str_replace('@', $id_collo, $carrier->url);
        }

        return Mailer::getCarrierTrackingURL($id_collo);
    }

    public function getCarrierName($id_order)
    {
        $order = new \Order($id_order);
        if (!\Validate::isLoadedObject($order)) {
            return '';
        }

        $carrier = new \Carrier($order->id_carrier, $this->id_lang);
        if (!\Validate::isLoadedObject($carrier)) {
            return '';
        }

        return $carrier->name;
    }

    public function getCarrierImage($params)
    {
        foreach ($params as $key => $param) {
            $id_carrier = 0;
            $id_order = 0;
            if ($key == 'id_carrier') {
                $id_carrier = (int) $param;
            }
            if ($key == 'id_order') {
                $id_order = (int) $param;
            }
            if ($id_order && !$id_carrier) {
                $order = new \Order($id_order);
                if (!\Validate::isLoadedObject($order)) {
                    $id_carrier = 0;
                    $id_order = 0;
                } else {
                    $id_carrier = $order->id_carrier;
                }
            }
            if ($id_carrier) {
                return $this->context->link->getMediaLink('/img/s/' . $id_carrier . '.jpg');
            }
        }

        return $this->context->link->getMediaLink('/404.jpg');
    }

    /**
     * Cerca il tracking nella tabella OrderCarrier
     * 
     * @param int $id_order
     *
     * @return string Se trova il tracking, prova a controllare se è un ID SPEDIZIONE (15 cifre) 
     *                Cerca il tracking number nel database Bartolini e se lo trova lo restituisce
     */
    public function getTrackingNumber($id_order)
    {
        $order = new \Order($id_order);
        if (!\Validate::isLoadedObject($order)) {
            return '';
        }

        $sql = new \DbQuery();
        $sql->select('tracking_number')
            ->from('order_carrier')
            ->where('id_order=' . (int) $order->id)
            ->where('id_carrier=' . (int) $order->id_carrier)
            ->orderBy('id_order_carrier DESC');

        $tracking_number = \Db::getInstance()->getValue($sql);

        return ConvertIdColloToTracking::convert($tracking_number);
    }
}
