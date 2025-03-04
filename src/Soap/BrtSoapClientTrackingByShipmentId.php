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
use MpSoft\MpBrtInfo\Bolla\BrtParseInfo;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BrtSoapClientTrackingByShipmentId extends BrtSoapClient
{
    const ENDPOINT = 'http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';
    const ENDPOINT_SSL = 'https://wsr.brt.it:10052/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';
    protected $endpoint;

    public function __construct()
    {
        $ssl = \ModelBrtConfig::useSSL();
        if ($ssl) {
            $this->endpoint = self::ENDPOINT_SSL;
        } else {
            $this->endpoint = self::ENDPOINT;
        }

        parent::__construct($this->endpoint);
    }

    public function getSoapTrackingByShipmentId($order_id, $spedizione_id = null, $spedizione_anno = '', $lang_iso = '')
    {
        $response = [];

        if (!$spedizione_anno) {
            $spedizione_anno = date('Y');
        }

        if (!$spedizione_id) {
            $this->errors[] = 'Spedizione non valida.';

            return false;
        }

        $request = new \stdClass();
        $request->LINGUA_ISO639_ALPHA2 = $lang_iso;
        $request->SPEDIZIONE_ANNO = $spedizione_anno;
        $request->SPEDIZIONE_BRT_ID = $spedizione_id;

        try {
            $response = $this->exec('BRT_TrackingByBRTshipmentID', ['arg0' => $request]);
            if (isset($response['return'])) {
                $response = $response['return'];
                $bolla = BrtParseInfo::parseTrackingInfo($response, \ModelBrtConfig::getEsiti());
                if ($order_id) {
                    $bolla->updateState($order_id);
                }

                return $bolla;
            } else {
                $this->errors[] = $response;

                return false;
            }
        } catch (\SoapFault $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
    }
}
