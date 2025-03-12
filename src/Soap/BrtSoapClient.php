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

ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 900);
ini_set('default_socket_timeout', 15);

/**
 * Classe BrtSoapClient che estende la classe SoapClient nativa di PHP
 * 
 * Questa classe aggiunge funzionalità di logging e gestione degli errori
 * per le chiamate SOAP ai servizi BRT.
 */
class BrtSoapClient extends \SoapClient
{
    /**
     * Array per memorizzare gli errori
     *
     * @var array
     */
    protected $errors = [];

    public const BASE_URL_SSL = 'https://wsr.brt.it:10052';
    public const BASE_URL = 'http://wsr.brt.it:10041';

    public function __construct($wsdl, $options = null)
    {
        $this->soapByFileGetContents($wsdl, $options);
    }

    protected function soapByFileGetContents($wsdl, $options)
    {
        $default = [
            'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
            'content-type' => 'application/xml',
            'verifypeer' => false,
            'verifyhost' => false,
            'soap_version' => SOAP_1_1,
        ];

        // Crea il contesto per la connessione SSL
        // In ambiente di produzione, è consigliabile mantenere le verifiche SSL attive
        // In ambiente di test o sviluppo, potrebbe essere necessario disabilitarle
        $verify_ssl = false;

        // Se siamo in ambiente di test o sviluppo, disabilita la verifica SSL
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
            $verify_ssl = false;
        }

        $arrContextOptions = stream_context_create([
            'ssl' => [
                'verify_peer' => $verify_ssl,
                'verify_peer_name' => $verify_ssl,
                'allow_self_signed' => !$verify_ssl,
                // Aumenta la compatibilità con server meno recenti
                'ciphers' => 'HIGH:!SSLv2:!SSLv3:!TLSv1.0',
            ],
            'http' => [
                'timeout' => 30,  // Timeout più lungo per le connessioni HTTP
            ]
        ]);
        $default = [
            'connection_timeout' => 15,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => true,
            'location' => $wsdl,
            'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'stream_context' => $arrContextOptions,
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'encoding' => 'UTF-8',
            'exceptions' => true,
            'content-type' => 'application/xml',
            'verifypeer' => false,
            'verifyhost' => false,
            'soap_version' => SOAP_1_1,
        ];

        if ($options) {
            $options = array_merge($options, $default);
        } else {
            $options = $default;
        }

        try {
            // Verifica se il WSDL è accessibile prima di inizializzare SoapClient
            $wsdl_content = @file_get_contents($wsdl, false, $arrContextOptions);
            if ($wsdl_content === false) {
                $error = error_get_last();
                if (property_exists($this, 'errors')) {
                    $this->errors[] = "Impossibile accedere al WSDL: " . ($error ? $error['message'] : 'Errore sconosciuto');
                    $this->errors[] = "URL WSDL: " . $wsdl;
                }

                // Tenta di usare una versione locale del WSDL se disponibile
                $local_wsdl = _PS_MODULE_DIR_ . 'mpbrtinfo/wsdl/' . basename($wsdl);
                if (file_exists($local_wsdl)) {
                    if (property_exists($this, 'errors')) {
                        $this->errors[] = "Utilizzo WSDL locale: " . $local_wsdl;
                    }
                    $wsdl = $local_wsdl;
                } else {
                    // Se non è disponibile una versione locale, continua comunque
                    // ma probabilmente fallirà
                    if (property_exists($this, 'errors')) {
                        $this->errors[] = "Nessun WSDL locale disponibile. Tentativo di connessione diretta.";
                    }
                }
            }

            parent::__construct($wsdl, $options);
        } catch (\SoapFault $sf) {
            if (property_exists($this, 'errors')) {
                $this->errors[] = "SoapFault: " . $sf->getMessage();
                $this->errors[] = "Codice: " . $sf->getCode();
            }
            if (property_exists($sf, 'detail')) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = "Dettagli: " . print_r($sf->detail, true);
                }
            }
        } catch (\Throwable $th) {
            if (property_exists($this, 'errors')) {
                $this->errors[] = "Errore generico: " . $th->getMessage();
                $this->errors[] = "File: " . $th->getFile() . " Line: " . $th->getLine();
            }
        }
    }

    /**
     * Esegue una chiamata SOAP al servizio BRT
     * 
     * @param string $function Nome della funzione da chiamare
     * @param array $params Parametri da passare alla funzione
     * 
     * @return array Risposta del servizio in formato array
     */
    public function exec($function, $params): array
    {
        try {
            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = 'Chiamata a ' . $function . ' con parametri: ' . json_encode($params);
                    $this->errors[] = 'Endpoint: ' . $this->__getLastRequestHeaders();
                }
            }

            // Esegui la chiamata SOAP
            $response = $this->$function($params);

            // Converti la risposta in array
            $result = json_decode(json_encode($response), true);

            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = 'Risposta: ' . json_encode($result);
                }
            }

            return $result;
        } catch (\SoapFault $e) {
            // Registra l'errore SOAP
            $error_msg = 'Errore SOAP: ' . $e->getMessage();
            if (property_exists($this, 'errors')) {
                $this->errors[] = $error_msg;
            }

            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = 'Ultima richiesta: ' . $this->__getLastRequest();
                    $this->errors[] = 'Ultimo header richiesta: ' . $this->__getLastRequestHeaders();
                    $this->errors[] = 'Ultima risposta: ' . $this->__getLastResponse();
                    $this->errors[] = 'Ultimo header risposta: ' . $this->__getLastResponseHeaders();
                }
            }

            return ['error' => $error_msg];
        } catch (\Exception $e) {
            // Registra altri errori
            $error_msg = 'Errore generico: ' . $e->getMessage();
            if (property_exists($this, 'errors')) {
                $this->errors[] = $error_msg;
            }
            return ['error' => $error_msg];
        }
    }

    /**
     * Esegue una chiamata SOAP al servizio BRT utilizzando le SOAP native di PHP
     * 
     * @param string $function Nome della funzione da chiamare
     * @param array $params Parametri da passare alla funzione
     * 
     * @return array Risposta del servizio in formato array
     */
    public function execSoap($function, $params): array
    {
        try {
            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = 'Chiamata SOAP a ' . $function . ' con parametri: ' . json_encode($params);
                    $this->errors[] = 'Endpoint: ' . $this->__getLastRequestHeaders();
                }
            }

            // Verifica che il client SOAP sia stato inizializzato correttamente
            if (!$this instanceof \SoapClient) {
                throw new \Exception('Client SOAP non inizializzato correttamente');
            }

            // Esegui la chiamata SOAP con i parametri forniti
            $response = $this->__soapCall($function, [$params], [
                'soapaction' => $function,
                'uri' => $this->__getLocation(),
            ]);

            // Converti la risposta in array
            $result = json_decode(json_encode($response), true);

            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = 'Risposta SOAP: ' . json_encode($result);
                    $this->errors[] = 'Ultima richiesta SOAP: ' . $this->__getLastRequest();
                    $this->errors[] = 'Ultima risposta SOAP: ' . $this->__getLastResponse();
                }
            }

            return $result;
        } catch (\SoapFault $e) {
            // Registra l'errore SOAP
            $error_msg = 'Errore SOAP: ' . $e->getMessage();
            if (property_exists($this, 'errors')) {
                $this->errors[] = $error_msg;
            }

            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = 'Ultima richiesta SOAP: ' . $this->__getLastRequest();
                    $this->errors[] = 'Ultimo header richiesta SOAP: ' . $this->__getLastRequestHeaders();
                    $this->errors[] = 'Ultima risposta SOAP: ' . $this->__getLastResponse();
                    $this->errors[] = 'Ultimo header risposta SOAP: ' . $this->__getLastResponseHeaders();
                    
                    if (property_exists($e, 'detail')) {
                        $this->errors[] = 'Dettagli errore SOAP: ' . print_r($e->detail, true);
                    }
                    $this->errors[] = 'Codice errore SOAP: ' . $e->getCode();
                    $this->errors[] = 'File errore SOAP: ' . $e->getFile() . ' Line: ' . $e->getLine();
                }
            }

            return ['error' => $error_msg, 'code' => $e->getCode()];
        } catch (\Exception $e) {
            // Registra altri errori
            $error_msg = 'Errore generico: ' . $e->getMessage();
            if (property_exists($this, 'errors')) {
                $this->errors[] = $error_msg;
            }
            
            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                if (property_exists($this, 'errors')) {
                    $this->errors[] = 'Codice errore: ' . $e->getCode();
                    $this->errors[] = 'File errore: ' . $e->getFile() . ' Line: ' . $e->getLine();
                }
            }
            
            return ['error' => $error_msg, 'code' => $e->getCode()];
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}