<?php

use MpSoft\MpBrtInfo\Helpers\CarrierAttributes;

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

use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetLegendaEsiti;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetLegendaEventi;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTotalShippings;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTrackingByIDC;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTrackingByRMA;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTrackingByRMN;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTrackingInfo;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTrackingNumbers;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTrackingsByShipmentId;

class MpBrtInfoCronModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        $this->name = 'Cron';
        $this->ajax = true;
        $this->auth = false;
        $this->guestAllowed = true;
        $this->ssl = (int) Configuration::get('PS_SSL_ENABLED');

        parent::__construct();

        $phpContent = file_get_contents('php://input');
        if ($phpContent) {
            $data = json_decode($phpContent, true);
            $action = 'displayAjax' . Tools::ucfirst($data['action']);
            if (method_exists($this, $action)) {
                $this->ajaxRender(json_encode($this->$action($data)));
                exit();
            }
        }
    }

    public function display()
    {
        exit('Display method not allowed');
    }

    protected function displayAjaxGetOrdersInfo()
    {
        $response = OrdersInfoGetTotalShippings::run();
        $ln = php_sapi_name() === 'cli' ? "\n" : '<br>';
        if ($response['status'] !== 'success') {
            return 'ERRORE DURANTE IL FETCH DEI DATI';
        }

        $getTracking = $response['list']['getTracking'];
        $getShipment = $response['list']['getShipment'];

        echo 'Trovate ' . $response['totalShippings'] . ' spedizioni da controllare' . $ln;

        echo 'Ricerca tracking per ' . count($getTracking) . ' spedizioni' . $ln;

        $responseNoTracking = OrdersInfoGetTrackingNumbers::run($getTracking);
        if ($responseNoTracking['status'] !== 'success') {
            echo 'ERRORE DURANTE IL CONTROLLO DEI TRACKING';
        }

        echo 'Trovate ' . $responseNoTracking['processed'] . ' spedizioni senza tracking' . $ln;

        $responseGetTracking = OrdersInfoGetTrackingsByShipmentId::run(['list' => $getShipment]);
        if ($responseGetTracking['status'] !== 'success') {
            echo 'ERRORE DURANTE IL CONTROLLO DEI TRACKING';
        }

        echo 'Trovate ' . $responseGetTracking['processed'] . ' spedizioni con tracking' . $ln;

        return $responseGetTracking;
    }

    protected function displayAjaxGetTrackingByBrtShipmentId($params)
    {
        $spedizioneId = $params['spedizione_id'];
        $spedizioneAnno = (int) $params['spedizione_anno'];
        $tracking = OrdersInfoGetTrackingInfo::get($spedizioneId, $spedizioneAnno);

        return $tracking;
    }

    protected function displayAjaxGetLegendaEsiti()
    {
        return OrdersInfoGetLegendaEsiti::get('IT', 0);
    }

    protected function displayAjaxGetLegendaEventi()
    {
        return OrdersInfoGetLegendaEventi::get('IT', 0);
    }

    protected function displayAjaxGetIdSpedizioneByRMN($params)
    {
        $rmn = $params['rmn'];
        $brtCustomerId = $params['brt_customer_id'];

        return OrdersInfoGetTrackingByRMN::get($brtCustomerId, $rmn);
    }

    protected function displayAjaxGetIdSpedizioneByRMA($params)
    {
        $rma = $params['rma'];
        $brtCustomerId = $params['brt_customer_id'];

        return OrdersInfoGetTrackingByRMA::get($brtCustomerId, $rma);
    }

    protected function displayAjaxGetIdSpedizioneByIDC($params)
    {
        $idc = $params['collo_id'];
        $brtCustomerId = $params['brt_customer_id'];

        return OrdersInfoGetTrackingByIDC::get($brtCustomerId, $idc);
    }

    protected function displayAjaxGetIdSpedizioneByIdCollo($params)
    {
        // TODO: eliminare questo metodo e usare displayAjaxGetIdSpedizioneByIDC
        return $this->displayAjaxGetIdSpedizioneByIDC($params);
    }

    protected function displayAjaxCarrierIcon($params)
    {
        $id_order = (int) $params['id_order'];
        if ($id_order <= 0) {
            return false;
        }

        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $carrier = new Carrier($order->id_carrier);
        if (!Validate::isLoadedObject($carrier)) {
            return false;
        }

        $evento = ModelBrtEvento::getEventFull($id_order);

        $carrierAttributes = new CarrierAttributes();
        $carrier_id = $carrier->id;
        $carrier_name = $carrier->name;
        $carrier_image = $carrierAttributes->getCarrierImage(['id_order' => $order->id, 'id_carrier' => $carrier_id]);
        $tracking_number = $carrierAttributes->getTrackingNumber($id_order);

        if ($evento && $evento['id_collo']) {
            $carrier_link = $carrierAttributes->getCarrierLink($carrier_id, $evento['id_collo']);
        } else {
            $carrier_link = 'javascript:void(0);';
        }

        if (!$evento || !$evento['event_id']) {
            $evento = [];
            $evento['id_order'] = $id_order;
            $evento['id_carrier'] = $carrier_id;
            $evento['carrier_link'] = $carrier_link;
            $evento['carrier_image'] = $carrier_image;
            $evento['carrier_name'] = $carrier_name;
            $evento['tracking_number'] = $tracking_number;
            $evento['isEmpty'] = true;
        }

        $tpl = $this->module->getLocalPath() . 'views/templates/admin/CarrierButton.tpl';
        $template = $this->context->smarty->createTemplate($tpl, $this->context->smarty);
        $template->assign([
            'event' => $evento,
        ]);

        return $template->fetch();
    }
}
