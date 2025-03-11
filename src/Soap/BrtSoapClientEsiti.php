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

        // Opzioni specifiche per questo client
        $options = [
            // Aumenta il timeout per dare più tempo alla connessione
            'connection_timeout' => 30,
            // Disabilita la cache WSDL per evitare problemi con WSDL obsoleti
            'cache_wsdl' => WSDL_CACHE_NONE,
            // Forza la versione SOAP 1.1 che è più compatibile
            'soap_version' => SOAP_1_1
        ];

        // Crea una directory locale per salvare i WSDL se non esiste
        $wsdl_dir = _PS_MODULE_DIR_ . 'mpbrtinfo/wsdl';
        if (!is_dir($wsdl_dir)) {
            mkdir($wsdl_dir, 0755, true);
        }

        parent::__construct($this->endpoint, $options);
    }

    /**
     * Ottiene la legenda degli esiti dal servizio BRT
     *
     * @param string $iso_lang Codice lingua ISO (es. 'IT')
     * @return array|false Array di esiti o false in caso di errore
     */
    public function getSoapLegendaEsiti($iso_lang = '')
    {
        $last_id = '';
        $response = [];
        $legenda = [];
        $esiti = [];
        $esito = 0;

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
                    $this->errors[] = 'Errore durante la chiamata SOAP';
                    $this->errors[] = $response;
                    if ($esiti) {
                        $this->errors[] = $esiti;
                    }

                    return false;
                }
            } catch (\SoapFault $e) {
                $this->errors[] = 'SoapFault in getSoapLegendaEsiti: ' . $e->getMessage();
                $this->errors[] = 'Endpoint: ' . $this->endpoint;
                $this->errors[] = 'Parametri: ISO=' . $iso_lang . ', ULTIMO_ID=' . $last_id;
                
                // Verifica se è un problema di connessione SSL
                if (strpos($e->getMessage(), 'SSL') !== false || 
                    strpos($e->getMessage(), 'certificate') !== false ||
                    strpos($e->getMessage(), 'failed to load external entity') !== false) {
                    $this->errors[] = 'Possibile problema di certificato SSL. Prova a disabilitare SSL nelle impostazioni.';
                }
                
                return false;
            } catch (\Exception $e) {
                $this->errors[] = 'Exception in getSoapLegendaEsiti: ' . $e->getMessage();
                return false;
            }
        } while ($esito != 100);

        return $esiti;
    }
}