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

namespace MpSoft\MpBrtInfo\WSDL;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpbrtinfo/models/autoload.php';

class GetLegendaEsiti extends BrtSoapClient
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

    /**
     * Crea l'oggetto di richiesta per la chiamata SOAP getlegendaesiti
     * 
     * @param string $language Codice lingua ISO 639 Alpha-2
     * @param string|int $last_id Ultimo ID ricevuto
     * @return object Oggetto di richiesta formattato secondo il WSDL
     */
    protected function createRequest(string $language = 'it', $last_id = 0)
    {
        // Crea l'oggetto di richiesta secondo la struttura del WSDL
        $input = new \stdClass();
        $input->LINGUA_ISO639_ALPHA2 = $language;
        $input->ULTIMO_ID_RICEVUTO = (int) $last_id;

        // Incapsula l'input in arg0 come richiesto dal WSDL
        $request = new \stdClass();
        $request->arg0 = $input;

        return $request;
    }

    /**
     * Ottiene la legenda degli esiti BRT utilizzando SOAP
     * 
     * @param string $iso_lang Codice lingua ISO 639 Alpha-2 (default: 'it')
     * @return array|false Array con i risultati o false in caso di errore
     */
    public function getLegendaEsiti($iso_lang = 'it', $last_id = 0)
    {
        $esito = 0;
        $legenda = [];

        do {
            try {
                // Crea la richiesta secondo il formato richiesto dal WSDL
                $request = $this->createRequest($iso_lang, $last_id);

                // Esegue la chiamata SOAP e ottiene il risultato
                $output = null;
                $result_code = null;

                // Chiamata SOAP usando il nome esatto dell'operazione dal WSDL: 'getlegendaesiti'
                // Non incapsulare ulteriormente i parametri, sono già formattati correttamente
                $success = $this->exec('getlegendaesiti', [$request], $output, $result_code);

                if ($success) {
                    // Verifica se $output è un oggetto e ha la proprietà return
                    if (is_object($output) && property_exists($output, 'return')) {
                        // Converti l'oggetto in array
                        $result = json_decode(json_encode($output->return), true);
                        $esito = isset($result['ESITO']) ? $result['ESITO'] : 0;

                        if (isset($result['LEGENDA_CONTATORE']) && isset($result['LEGENDA'])) {
                            $contatore = $result['LEGENDA_CONTATORE'];
                            $list = array_splice($result['LEGENDA'], 0, $contatore);
                            foreach ($list as $item) {
                                $legenda[] = $item;
                            }
                            if (!empty($list)) {
                                $last_item = end($list);
                                $last_id = isset($last_item['ID']) ? $last_item['ID'] : 0;
                            }
                        } else {
                            // Se non ci sono risultati, interrompi il ciclo
                            $esito = 0;
                        }
                    } else {
                        // Output è un array o non ha la proprietà return
                        $this->errors[] = "Formato di risposta SOAP non valido";
                        return false;
                    }
                } else {
                    $this->errors[] = "Nessun risultato valido dalla chiamata SOAP";
                    return false;
                }
            } catch (\Throwable $th) {
                $this->errors[] = 'getLegendaEsiti: request -> ' . print_r($request, 1);
                $this->errors[] = 'getLegendaEsiti: error -> ' . $th->getMessage();
                return false;
            }
        } while ($esito == 100); // Continua se ci sono altri risultati da recuperare

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