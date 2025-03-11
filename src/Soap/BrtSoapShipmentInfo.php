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

namespace MpSoft\MpBrtInfo\Soap;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpbrtinfo/models/autoload.php';

class BrtSoapShipmentInfo extends BrtSoapClient
{
    protected $shipment_id;
    protected $year;

    public function __construct($shipment_id, $year)
    {
        $url = '';
        $ssl = \ModelBrtConfig::useSSL();
        if ($ssl) {
            $url = 'https://wsr.brt.it:10052/web/TrackingByBRTshipmentIDService/TrackingByBRTshipmentID?wsdl';
        } else {
            $url = 'http://wsr.brt.it:10041/web/TrackingByBRTshipmentIDService/TrackingByBRTshipmentID?wsdl';
        }
        $this->shipment_id = $shipment_id;
        $this->year = $year;

        parent::__construct($url);
    }

    protected function createRequest()
    {
        $request = new \stdClass();
        $request->LINGUA_ISO639_ALPHA2 = '';
        $request->SPEDIZIONE_ANNO = $this->year;
        $request->SPEDIZIONE_BRT_ID = $this->shipment_id;

        return $request;
    }

    public static function getTrackingNumber($id_order, $id_carrier)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('tracking_number')
            ->from('order_carrier')
            ->where('id_order=' . (int) $id_order)
            ->where('id_carrier=' . (int) $id_carrier);
        $tracking_number = $db->getValue($sql);

        return $tracking_number;
    }

    public function getShipmentInfoBySoap($shipment_id, $year)
    {
        $request = $this->createRequest();
        try {
            $response = $this->exec('brt_trackingbybrtshipmentid', ['arg0' => $request]);
            if (isset($response['return'])) {
                return $response['return'];
            }
        } catch (\Throwable $th) {
            $this->errors[] = 'getShipmentId: request -> ' . print_r($request, 1);
            $this->errors[] = 'getShipmentId: error -> ' . $th->getMessage();
            return false;
        }

        return [];
    }

    public function getLastEvento($shipment_id = null, $year = null)
    {
        if (!$shipment_id) {
            $shipment_id = $this->shipment_id;
        }
        if (!$year) {
            $year = $this->year;
        }
        $eventi = $this->getShipmentInfoBySoap($shipment_id, $year);
        if (isset($eventi['ESITO']) && $eventi['ESITO'] == 0) {
            $evento = $eventi['LISTA_EVENTI'][0];

            return $evento;
        } else {
            $error = \ModelBrtEsito::getByIdEsito($eventi['ESITO']);
            if (\Validate::isLoadedObject($error)) {
                $data = [
                    'id_esito' => $error->id_esito,
                    'testo1' => $error->testo1,
                    'testo2' => $error->testo2,
                ];

                return [
                    'error' => true,
                    'esito' => $data,
                ];
            }
        }

        return false;
    }
}
