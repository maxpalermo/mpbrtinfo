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

class BrtSoapEsiti extends BrtSoapClient
{
    const ENDPOINT =
        'http://wsr.brt.it:10041/web/GetLegendaEsitiService/GetLegendaEsiti?wsdl';
    const ENDPOINT_SSL =
        'https://wsr.brt.it:10052/web/GetLegendaEsitiService/GetLegendaEsiti?wsdl';

    public function __construct()
    {
        $ssl = \ModelBrtConfig::useSSL();
        $endpoint = $ssl ? self::ENDPOINT_SSL : self::ENDPOINT;
        
        parent::__construct($endpoint);
    }

    protected function createRequest(string $language = '', string $last_id = '')
    {
        $request = new \stdClass();
        $request->LINGUA_ISO639_ALPHA2 = $language;
        $request->ULTIMO_ID_RICEVUTO = $last_id;

        return $request;
    }

    public function getLegendaEsiti($iso_lang = '')
    {
        $last_id = '';
        $esito = 0;
        $legenda = [];

        do {
            $request = $this->createRequest($iso_lang, $last_id);

            try {
                $response = $this->exec('getlegendaesiti', ['arg0' => $request]);
                if (isset($response['return'])) {
                    $result = $response['return'];
                    $esito = $result['ESITO'];
                    $contatore = $result['LEGENDA_CONTATORE'];
                    $list = array_splice($result['LEGENDA'], 0, $contatore);
                    foreach ($list as $item) {
                        $legenda[] = $item;
                    }
                    $last_id = $item['ID'];
                }
            } catch (\Throwable $th) {
                $this->errors[] = 'getLegendaEsiti: request -> ' . print_r($request, 1);
                $this->errors[] = 'getLegendaEsiti: error -> ' . $th->getMessage();

                return false;
            }
        } while ($esito == 100);

        return $legenda;
    }

    public function getEsiti($iso_lang = '', $last_id = '')
    {
        $request = $this->createRequest($iso_lang, $last_id);
        try {
            $response = $this->exec('getlegendaesiti', ['arg0' => $request]);
            if (isset($response['return'])) {
                return $response['return'];
            }
        } catch (\Throwable $th) {
            $this->errors[] = 'getLegendaEsiti: request -> ' . print_r($request, 1);
            $this->errors[] = 'getLegendaEsiti: error -> ' . $th->getMessage();
            return false;
        }

        return [];
    }
}