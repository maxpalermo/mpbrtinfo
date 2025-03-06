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
        define('_PS_VERSION_', '1.7.8.0');
    } else {
        exit;
    }
}

/**
 * Client SOAP per ottenere le informazioni di spedizione tramite tracking
 * 
 * Implementa il web service GetIdSpedizioneByRMN che consente di ottenere
 * l'ID di una spedizione BRT utilizzando il riferimento mittente numerico e l'ID cliente.
 */
class GetIdSpedizioneInfo extends BrtSoapClient
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

    protected $tracking_number;
    protected $year;

    /**
     * Costruttore
     * 
     * Inizializza il client SOAP con l'endpoint appropriato in base alla configurazione
     */
    public function __construct($tracking_number, $year, $use_ssl = true)
    {
        $this->tracking_number = $tracking_number;
        $this->year = $year;

        // Verifica se la classe ModelBrtConfig esiste e se il metodo useSSL è disponibile
        if (class_exists('\\ModelBrtConfig') && method_exists('\\ModelBrtConfig', 'useSSL')) {
            $ssl = \ModelBrtConfig::useSSL();
        } else {
            // Default a true se non è possibile determinare dalla configurazione
            $ssl = $use_ssl;
        }

        $this->endpoint = $ssl ? self::ENDPOINT_SSL : self::ENDPOINT;

        parent::__construct($this->endpoint);
    }

    /**
     * Ottiene l'ID di una spedizione BRT tramite riferimento mittente numerico
     * 
     * @return \MpSoft\MpBrtInfo\Bolla\Bolla|false Array con l'ID spedizione o false in caso di errore
     */
    public function getInfo()
    {
        if (empty($this->tracking_number)) {
            $this->errors[] = 'Tracking number non valido';

            return false;
        }

        if (empty($this->year)) {
            $this->year = date('Y');
        }

        // Prepara la richiesta SOAP
        $request = new \stdClass();
        $request->LINGUA_ISO639_ALPHA2 = '';
        $request->SPEDIZIONE_ANNO = $this->year;
        $request->SPEDIZIONE_BRT_ID = $this->tracking_number;

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
                        '-11' => 'Spedizione non trovata',
                        '-20' => 'Riferimento mittente numerico non ricevuto',
                        '-21' => 'ID cliente non ricevuto o non valido',
                        '-22' => 'Trovata più di una spedizione',
                    ];

                    $error_code = $result['ESITO'];
                    $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Errore sconosciuto';

                    $this->errors[] = "Errore BRT (codice {$error_code}): {$error_message}";

                    return false;
                }

                // Analizza la risposta e crea un oggetto con i dati della spedizione
                $shipment_data = BrtParseInfo::parseTrackingInfo($result, \ModelBrtConfig::getEsiti());

                // Restituisci l'ID spedizione
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
}
