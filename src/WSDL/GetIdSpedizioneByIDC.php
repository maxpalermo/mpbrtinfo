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

/**
 * Client SOAP per ottenere l'ID spedizione BRT tramite ID collo
 * 
 * Implementa il web service GetIdSpedizioneByIdCollo che consente di ottenere
 * l'ID di una spedizione BRT utilizzando l'ID collo e l'ID cliente.
 */
class GetIdSpedizioneByIDC extends BrtSoapClient
{
    /**
     * Endpoint HTTP (non SSL)
     */
    const ENDPOINT = 'http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';

    /**
     * Endpoint HTTPS (SSL)
     */
    const ENDPOINT_SSL = 'https://wsr.brt.it:10052/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';

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
     * Crea l'oggetto di richiesta per la chiamata SOAP getidspedizionebyidcollo
     * 
     * @param string|int $cliente_id ID del cliente BRT
     * @param string $collo_id ID del collo
     *
     * @return object Oggetto di richiesta formattato secondo il WSDL
     */
    protected function createRequest($cliente_id, $collo_id)
    {
        // Verifica e converte i parametri come richiesto dal WSDL
        $cliente_id = (float) $cliente_id;

        // Crea l'oggetto di input secondo la struttura del WSDL
        $input = new \stdClass();
        $input->CLIENTE_ID = $cliente_id;
        $input->COLLO_ID = $collo_id;

        // Incapsula l'input in arg0 come richiesto dal WSDL
        $request = new \stdClass();
        $request->arg0 = $input;

        return $request;
    }

    /**
     * Ottiene l'ID di una spedizione BRT tramite ID collo
     * 
     * @param string|int $cliente_id ID del cliente BRT
     * @param string $collo_id ID del collo
     *
     * @return array|false Array con l'ID spedizione o false in caso di errore
     */
    public function getIdSpedizione($cliente_id, $collo_id)
    {
        // Verifica i parametri obbligatori
        if (empty($collo_id)) {
            $this->errors[] = 'ID collo non valido';

            return false;
        }

        if (empty($cliente_id)) {
            $this->errors[] = 'ID cliente BRT non valido';

            return false;
        }

        try {
            // Crea la richiesta secondo il formato richiesto dal WSDL
            $request = $this->createRequest($cliente_id, $collo_id);

            // Esegue la chiamata SOAP e ottiene il risultato
            $output = null;
            $result_code = null;

            // Chiamata SOAP usando il nome esatto dell'operazione dal WSDL: 'getidspedizionebyidcollo'
            $success = $this->exec('getidspedizionebyidcollo', [$request], $output, $result_code);

            if ($success) {
                // Verifica se $output è un oggetto e ha la proprietà return
                if (is_object($output) && property_exists($output, 'return')) {
                    // Converti l'oggetto in array
                    $result = json_decode(json_encode($output->return), true);

                    // Verifica l'esito della chiamata
                    if (isset($result['ESITO']) && $result['ESITO'] < 0) {
                        $error_messages = [
                            '-1' => 'Errore generico/sconosciuto',
                            '-3' => 'Errore connessione database server',
                            '-11' => 'Spedizione non trovata',
                            '-20' => 'ID collo non ricevuto',
                            '-21' => 'ID cliente non ricevuto o non valido',
                            '-22' => 'Trovata più di una spedizione',
                        ];

                        $error_code = $result['ESITO'];
                        $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Errore sconosciuto';

                        $this->errors[] = "Errore BRT (codice {$error_code}): {$error_message}";

                        return false;
                    }

                    // Restituisci l'ID spedizione e altri dati
                    return [
                        'esito' => $result['ESITO'],
                        'spedizione_id' => isset($result['SPEDIZIONE_ID']) ? $result['SPEDIZIONE_ID'] : '',
                        'spedizione_anno' => isset($result['SPEDIZIONE_ANNO']) ? $result['SPEDIZIONE_ANNO'] : '',
                        'versione' => isset($result['VERSIONE']) ? $result['VERSIONE'] : '',
                    ];
                } else {
                    // Output è un array o non ha la proprietà return
                    $this->errors[] = 'Formato di risposta SOAP non valido';

                    return false;
                }
            } else {
                $this->errors[] = 'Nessun risultato valido dalla chiamata SOAP';

                return false;
            }
        } catch (\SoapFault $e) {
            $this->errors[] = 'Errore SOAP: ' . $e->getMessage();

            return false;
        } catch (\Throwable $th) {
            $this->errors[] = 'getIdSpedizione: request -> ' . print_r($request, 1);
            $this->errors[] = 'getIdSpedizione: error -> ' . $th->getMessage();

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

    /**
     * Aggiunge un separatore (divider) nelle bulk actions di PrestaShop
     * 
     * @param string $key Chiave del separatore
     *
     * @return array Configurazione del separatore
     */
    public function addDivider($key = 'divider1')
    {
        return [
            $key => [
                'text' => 'divider',
            ],
        ];
    }
}
