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

class BrtSoapClientEsiti extends BrtSoapClient
{
    const ENDPOINT =
        'http://wsr.brt.it:10041/web/GetLegendaEsitiService/GetLegendaEsiti?wsdl';
    const ENDPOINT_SSL =
        'https://wsr.brt.it:10052/web/GetLegendaEsitiService/GetLegendaEsiti?wsdl';
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

    public function getSoapLegendaEsiti($iso_lang = '')
    {
        $last_id = '';
        $response = [];
        $legenda = [];
        $esiti = [];
        $esito = 0;
        $legenda = [];

        do {
            $request = new \stdClass();
            $request->LINGUA_ISO639_ALPHA2 = $iso_lang;
            $request->ULTIMO_ID_RICEVUTO = $last_id;

            try {
                $response = $this->exec('GetLegendaEsiti', ['arg0' => $request]);
                if (isset($response['return'])) {
                    $response = $response['return'];
                    $contatore = (int) isset($response['LEGENDA_CONTATORE']) ? $response['LEGENDA_CONTATORE'] : 0;
                    $legenda = array_splice($response['LEGENDA'], 0, $contatore);
                    $esiti = array_merge($esiti, $legenda);
                    $last_id = end($legenda)['ID'];
                    $esito = (int) $response['ESITO'];
                } else {
                    $this->errors[] = $response;

                    return false;
                }
            } catch (\SoapFault $e) {
                $this->errors[] = $e->getMessage();

                return false;
            }
        } while ($esito != 100);

        return $esiti;
    }
}
