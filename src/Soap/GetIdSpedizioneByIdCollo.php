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
 * Client SOAP per ottenere l'ID spedizione BRT tramite ID collo
 * 
 * Implementa il web service GetIdSpedizioneByIdCollo che consente di ottenere
 * l'ID di una spedizione BRT utilizzando l'ID collo e l'ID cliente.
 */
class GetIdSpedizioneByIdCollo extends BrtSoapClient
{
    /**
     * Endpoint HTTP (deprecato)
     */
    const ENDPOINT = 'http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';
    
    /**
     * Endpoint HTTPS (raccomandato)
     */
    const ENDPOINT_SSL = 'https://wsr.brt.it:10052/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';
    
    /**
     * Endpoint attualmente in uso
     * 
     * @var string
     */
    protected $endpoint;
    
    /**
     * Costruttore
     * 
     * Inizializza il client SOAP con l'endpoint appropriato in base alla configurazione
     * 
     * @param bool $use_ssl Flag per forzare l'uso di SSL se la classe ModelBrtConfig non è disponibile
     */
    public function __construct($use_ssl = true)
    {
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
     * Ottiene l'ID di una spedizione BRT tramite ID collo
     * 
     * @param string $collo_id ID del collo
     * @param string $cliente_id ID cliente BRT
     * 
     * @return array|false Array con l'ID spedizione e l'anno spedizione o false in caso di errore
     */
    public function getIdSpedizione($collo_id, $cliente_id)
    {
        if (empty($collo_id)) {
            $this->errors[] = 'ID collo non valido';
            return false;
        }

        if (empty($cliente_id)) {
            $this->errors[] = 'ID cliente BRT non valido';
            return false;
        }

        // Prepara la richiesta SOAP
        $request = new \stdClass();
        $request->COLLO_ID = $collo_id;
        $request->CLIENTE_ID = $cliente_id;

        try {
            // Esegui la chiamata SOAP
            $response = $this->exec('GetIdSpedizioneByIdCollo', ['arg0' => $request]);
            
            // Verifica la risposta
            if (isset($response['return'])) {
                $result = $response['return'];
                
                // Verifica l'esito della chiamata
                if (isset($result['ESITO']) && $result['ESITO'] < 0) {
                    $error_messages = [
                        '-1' => 'Errore generico/sconosciuto',
                        '-3' => 'Errore connessione database server',
                        '-11' => 'Spedizione non trovata',
                        '-21' => 'ID cliente non ricevuto',
                        '-22' => 'Trovata più di una spedizione',
                        '-30' => 'ID collo non ricevuto',
                    ];
                    
                    $error_code = $result['ESITO'];
                    $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Errore sconosciuto';
                    
                    $this->errors[] = "Errore BRT (codice {$error_code}): {$error_message}";
                    return false;
                }
                
                // Restituisci l'ID spedizione e l'anno spedizione
                return [
                    'esito' => $result['ESITO'],
                    'spedizione_id' => $result['SPEDIZIONE_ID'] ?? '',
                    'spedizione_anno' => $result['SPEDIZIONE_ANNO'] ?? '',
                ];
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
