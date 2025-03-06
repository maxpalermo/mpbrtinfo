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

ini_set('default_socket_timeout', 600);

if (!defined('_PS_VERSION_')) {
    exit;
}
class BrtSoap
{
    protected $errors = [];
    protected $name;
    protected $wsdl;
    protected $client;
    const URL_GET_SHIPMENT_INFO =
        'http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';
    const URL_GET_SHIPMENT_RMN =
        'http://wsr.brt.it:10041/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl';
    const URL_GET_SHIPMENT_RMA =
        'http://wsr.brt.it:10041/web/GetIdSpedizioneByRMAService/GetIdSpedizioneByRMA?wsdl';
    const URL_GET_SHIPMENT_COLLO =
        'http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';
    const URL_GET_ESITI =
        'http://wsr.brt.it:10041/web/GetLegendaEsitiService/GetLegendaEsiti?wsdl';

    const URL_GET_SHIPMENT_INFO_SSL =
        'https://wsr.brt.it:10052/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';
    const URL_GET_SHIPMENT_RMN_SSL =
        'https://wsr.brt.it:10052/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl';
    const URL_GET_SHIPMENT_RMA_SSL =
        'https://wsr.brt.it:10052/web/GetIdSpedizioneByRMAService/GetIdSpedizioneByRMA?wsdl';
    const URL_GET_SHIPMENT_COLLO_SSL =
        'https://wsr.brt.it:10052/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';
    const URL_GET_ESITI_SSL =
        'https://wsr.brt.it:10052/web/GetLegendaEsitiService/GetLegendaEsiti?wsdl';

    const URL_GET_SHIPMENT_BY_RMN =
    'http://wsr.brt.it:10041/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl';
    const URL_GET_SHIPMENT_BY_RMN_SSL =
        'https://wsr.brt.it:10052/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl';
    const URL_GET_SHIPMENT_BY_RMA =
        'http://wsr.brt.it:10041/web/GetIdSpedizioneByRMAService/GetIdSpedizioneByRMA?wsdl';
    const URL_GET_SHIPMENT_BY_RMA_SSL =
        'https://wsr.brt.it:10052/web/GetIdSpedizioneByRMAService/GetIdSpedizioneByRMA?wsdl';
    const URL_GET_SHIPMENT_BY_ID =
        'http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';
    const URL_GET_SHIPMENT_BY_ID_SSL =
        'https://wsr.brt.it:10052/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdColloN?wsdl';

    public function __construct($wsdl)
    {
        $this->errors = [];
        $this->wsdl = $wsdl;
    }

    protected function createSoapClient()
    {
        try {
            $client = new \SoapClient($this->wsdl);
        } catch (\Exception $exc) {
            $this->errors[] = $exc->getMessage();

            return false;
        }
        $this->client = $client;

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getClient()
    {
        if ($this->createSoapClient()) {
            return $this->client;
        }

        return false;
    }
}