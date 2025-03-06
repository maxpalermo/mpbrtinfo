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

// Verifica che la costante _PS_VERSION_ sia definita
if (!defined('_PS_VERSION_')) {
    // Se eseguito direttamente, non uscire ma definisci la costante per i test
    if (!defined('_PS_ROOT_DIR_')) {
        define('_PS_VERSION_', '8.2.0');
    }
}

/**
 * Client SOAP per il tracking delle spedizioni BRT tramite ID spedizione
 * 
 * Implementa il web service BRT_TrackingByBRTshipmentID che consente di ottenere
 * informazioni dettagliate su una spedizione BRT utilizzando il numero di spedizione.
 */

// Carica l'autoloader del modulo se la costante _PS_MODULE_DIR_ è definita
if (defined('_PS_MODULE_DIR_')) {
    $autoload_file = _PS_MODULE_DIR_ . 'mpbrtinfo/models/autoload.php';
    if (file_exists($autoload_file)) {
        require_once $autoload_file;
    }
}
class TrackingByBRTshipmentID extends BrtSoapClient
{
    /**
     * Endpoint HTTP (deprecato)
     */
    const ENDPOINT = 'http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';

    /**
     * Endpoint HTTPS (raccomandato)
     */
    const ENDPOINT_SSL = 'https://wsr.brt.it:10052/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';

    /**
     * Endpoint attualmente in uso
     * 
     * @var string
     */
    protected $endpoint;
    protected $id_order;
    protected $tracking;
    protected $year_shipped;
    protected $lang_iso;

    /**
     * Costruttore
     * 
     * Inizializza il client SOAP con l'endpoint appropriato in base alla configurazione
     */
    public function __construct($id_order, $tracking, $year_shipped = null, $lang_iso = '')
    {
        $ssl = \ModelBrtConfig::useSSL();
        $this->endpoint = $ssl ? self::ENDPOINT_SSL : self::ENDPOINT;

        parent::__construct($this->endpoint);

        $this->id_order = $id_order;
        $this->tracking = $tracking;
        $this->year_shipped = $year_shipped ?? date('Y');
        $this->lang_iso = $lang_iso;
    }

    /**
     * Restituisce gli errori di richiesta
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Ottiene i dettagli di una spedizione BRT tramite il suo ID
     * 
     * @param string $tracking_number Numero di spedizione BRT nel formato FFFSSNNNNNNN
     * @param string $lang_iso Codice lingua ISO 639 Alpha-2 (opzionale, vuoto = italiano)
     * @param string $year Anno della spedizione (opzionale, vuoto = anno corrente)
     * 
     * @return \MpSoft\MpBrtInfo\Bolla\Bolla|false Oggetto con i dettagli della spedizione o false in caso di errore
     */
    public function getTracking()
    {
        if (empty($this->tracking)) {
            $this->errors[] = 'Numero di spedizione non valido';

            return false;
        }

        $tracking_number = str_pad($this->tracking, 12, '0');
        $year = $this->year_shipped;
        $lang_iso = $this->lang_iso;

        // Prepara la richiesta SOAP
        $request = new \stdClass();
        $request->LINGUA_ISO639_ALPHA2 = $lang_iso;
        $request->SPEDIZIONE_ANNO = $year;
        $request->SPEDIZIONE_BRT_ID = $tracking_number;

        try {
            // Esegui la chiamata SOAP
            $response = $this->exec('BRT_TrackingByBRTshipmentID', ['arg0' => $request]);

            // Verifica la risposta
            if (isset($response['return'])) {
                $result = $response['return'];

                // Verifica l'esito della chiamata
                if (isset($result['ESITO']) && $result['ESITO'] < 0) {
                    $error_messages = [
                        '-1' => 'Errore generico/sconosciuto',
                        '-3' => 'Errore connessione database server',
                        '-10' => 'ID spedizione BRT non ricevuta',
                        '-11' => 'Spedizione non trovata',
                    ];

                    $error_code = $result['ESITO'];
                    $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Errore sconosciuto';

                    $this->errors[] = "Errore BRT (codice {$error_code}): {$error_message}";

                    return false;
                }

                // Analizza la risposta e crea un oggetto con i dati della spedizione
                $shipment_data = BrtParseInfo::parseTrackingInfo($result, \ModelBrtConfig::getEsiti());

                // Se è disponibile l'ID dell'ordine, aggiorna lo stato
                if ($this->id_order) {
                    $shipment_data->updateState($this->id_order);
                }

                return $shipment_data;
            } else {
                $this->errors[] = 'Risposta non valida dal server BRT';

                return false;
            }
        } catch (\SoapFault $e) {
            $this->errors[] = 'Errore SOAP: ' . $e->getMessage();

            return false;
        } catch (\Exception $e) {
            $this->errors[] = 'Errore: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * Estrae i dati principali dalla risposta SOAP
     * 
     * @param array $response Risposta SOAP
     *
     * @return array Dati estratti dalla risposta
     */
    public function extractShipmentData($response)
    {
        $data = [];

        if (isset($response['BOLLA'])) {
            $shipment = $response['BOLLA'];

            // Dati spedizione
            if (isset($shipment['DATI_SPEDIZIONE'])) {
                $shipping_data = $shipment['DATI_SPEDIZIONE'];
                $data['id_spedizione'] = $shipping_data['SPEDIZIONE_ID'] ?? '';
                $data['data_spedizione'] = $shipping_data['SPEDIZIONE_DATA'] ?? '';
                $data['porto'] = $shipping_data['PORTO'] ?? '';
                $data['servizio'] = $shipping_data['SERVIZIO'] ?? '';
                $data['tipo_porto'] = $shipping_data['TIPO_PORTO'] ?? '';
                $data['tipo_servizio'] = $shipping_data['TIPO_SERVIZIO'] ?? '';
                $data['stato_parte1'] = $shipping_data['STATO_SPED_PARTE1'] ?? '';
                $data['stato_parte2'] = $shipping_data['STATO_SPED_PARTE2'] ?? '';
                $data['descrizione_stato_parte1'] = $shipping_data['DESCRIZIONE_STATO_SPED_PARTE1'] ?? '';
                $data['descrizione_stato_parte2'] = $shipping_data['DESCRIZIONE_STATO_SPED_PARTE2'] ?? '';
            }

            // Dati merce
            if (isset($shipment['MERCE'])) {
                $goods_data = $shipment['MERCE'];
                $data['colli'] = $goods_data['COLLI'] ?? '';
                $data['peso'] = $goods_data['PESO_KG'] ?? '';
                $data['volume'] = $goods_data['VOLUME_M3'] ?? '';
                $data['natura'] = $goods_data['NATURA_MERCE'] ?? '';
            }

            // Dati consegna
            if (isset($shipment['DATI_CONSEGNA'])) {
                $delivery_data = $shipment['DATI_CONSEGNA'];
                $data['data_consegna'] = $delivery_data['DATA_CONSEGNA_MERCE'] ?? '';
                $data['ora_consegna'] = $delivery_data['ORA_CONSEGNA_MERCE'] ?? '';
                $data['firmatario'] = $delivery_data['FIRMATARIO_CONSEGNA'] ?? '';
            }
        }

        // Eventi
        if (isset($response['LISTA_EVENTI']) && is_array($response['LISTA_EVENTI'])) {
            $data['eventi'] = [];
            foreach ($response['LISTA_EVENTI'] as $event) {
                $data['eventi'][] = [
                    'id' => $event['ID'] ?? '',
                    'data' => $event['DATA'] ?? '',
                    'ora' => $event['ORA'] ?? '',
                    'descrizione' => $event['DESCRIZIONE'] ?? '',
                    'filiale' => $event['FILIALE'] ?? '',
                ];
            }
        }

        return $data;
    }
}