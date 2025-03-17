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

use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;

/**
 * Client SOAP per ottenere il tracking di una spedizione BRT tramite l'ID spedizione BRT
 * 
 * Implementa il web service BRT_TrackingByBRTshipmentID che consente di ottenere
 * le informazioni di tracking di una spedizione BRT utilizzando l'ID spedizione e l'anno.
 */
class GetTrackingByBrtShipmentId extends BrtSoapClient
{
    /**
     * Endpoint HTTP (non SSL)
     */
    const ENDPOINT = 'http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';

    /**
     * Endpoint HTTPS (SSL)
     */
    const ENDPOINT_SSL = 'https://wsr.brt.it:10052/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl';

    /**
     * Array per memorizzare gli errori
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Costruttore
     * 
     * Inizializza il client SOAP con l'endpoint appropriato in base alla configurazione SSL
     */
    public function __construct()
    {
        $ssl = \ModelBrtConfig::useSSL();
        $endpoint = $ssl ? self::ENDPOINT_SSL : self::ENDPOINT;

        parent::__construct($endpoint);
    }

    /**
     * Crea l'oggetto di richiesta per la chiamata SOAP brt_trackingbybrtshipmentid
     * 
     * @param string $spedizione_anno Anno della spedizione
     * @param string $spedizione_brt_id ID della spedizione BRT
     * @param string $lingua_iso639_alpha2 Codice lingua ISO639 (default: IT)
     * @return object Oggetto di richiesta formattato secondo il WSDL
     */
    protected function createRequest($spedizione_anno, $spedizione_brt_id, $lingua_iso639_alpha2 = 'IT')
    {
        // Crea l'oggetto di input secondo la struttura del WSDL
        $input = new \stdClass();
        $input->SPEDIZIONE_ANNO = (string) $spedizione_anno;
        $input->SPEDIZIONE_BRT_ID = (string) $spedizione_brt_id;
        $input->LINGUA_ISO639_ALPHA2 = (string) $lingua_iso639_alpha2;

        // Incapsula l'input in arg0 come richiesto dal WSDL
        $request = new \stdClass();
        $request->arg0 = $input;

        return $request;
    }

    /**
     * Ottiene le informazioni di tracking di una spedizione BRT tramite l'ID spedizione
     * 
     * @param string $spedizione_brt_id ID della spedizione BRT
     * @param int $id_order ID dell'ordine PrestaShop (per ricavare l'anno di spedizione)
     * @param string $lingua_iso639_alpha2 Codice lingua ISO639 (default: IT)
     * @return array|false Array con le informazioni di tracking o false in caso di errore
     */
    public function getTracking($spedizione_brt_id, $id_order, $lingua_iso639_alpha2 = 'IT', $spedizione_anno = '')
    {
        // Verifica i parametri obbligatori
        if (empty($spedizione_brt_id)) {
            $this->errors[] = 'ID spedizione BRT non valido';
            return false;
        }

        try {
            if (empty($id_order) && empty($spedizione_anno)) {
                $spedizione_anno = date('Y');
            } elseif (!empty($spedizione_anno)) {
                $spedizione_anno = (int) $spedizione_anno;
            } else {
                // Ottieni l'anno di spedizione dall'ID ordine
                $orderShippingDate = new GetOrderShippingDate($id_order);
                $spedizione_anno = $orderShippingDate->getShippingYear();
            }

            if (!$spedizione_anno) {
                $this->errors[] = 'Impossibile determinare l\'anno di spedizione per l\'ordine ' . $id_order;
                return false;
            }

            // Crea la richiesta secondo il formato richiesto dal WSDL
            $request = $this->createRequest($spedizione_anno, $spedizione_brt_id, $lingua_iso639_alpha2);

            // Prepara i parametri per la chiamata SOAP
            $output = null;
            $result_code = null;

            // Esegue la chiamata SOAP utilizzando il metodo exec della classe BrtSoapClient
            if ($this->soapClient) {
                $success = $this->soapClient->__soapCall('brt_trackingbybrtshipmentid', [$request]);

                if (!$success) {
                    $this->errors[] = 'Errore nella chiamata SOAP';
                    return false;
                }

                // Converti la risposta in array
                $output = $success;
            } else {
                $this->errors[] = 'Client SOAP non inizializzato correttamente';
                return false;
            }

            // Verifica se $output è un oggetto e ha la proprietà return
            if (is_object($output) && property_exists($output, 'return')) {
                // Converti l'oggetto in array
                $response = json_decode(json_encode($output->return), true);
            } else {
                // Output è un array o non ha la proprietà return
                $this->errors[] = 'Risposta SOAP non valida: campo "return" mancante';
                return false;
            }

            // Verifica l'esito della chiamata
            if (isset($response['ESITO']) && $response['ESITO'] < 0) {
                $error_messages = [
                    '-1' => 'Errore generico/sconosciuto',
                    '-3' => 'Errore connessione database server',
                    '-11' => 'Spedizione non trovata',
                    '-20' => 'ID spedizione BRT non ricevuto',
                    '-21' => 'Anno spedizione non ricevuto o non valido',
                ];

                $error_code = $response['ESITO'];
                $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Errore sconosciuto';

                $this->errors[] = "Errore BRT (codice {$error_code}): {$error_message}";
                return false;
            }

            // Restituisci i dati di tracking
            return $response;
        } catch (\SoapFault $e) {
            $this->errors[] = 'Errore SOAP: ' . $e->getMessage();
            return false;
        } catch (\Throwable $th) {
            $this->errors[] = 'getTracking: error -> ' . $th->getMessage();
            return false;
        }
    }

    /**
     * Restituisce gli errori accumulati durante l'esecuzione
     * 
     * @return array Lista degli errori
     */
    public function getErrors()
    {
        return $this->errors;
    }
}