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

class BrtSoapClient extends \SoapClient
{
    protected $errors = [];

    public const BASE_URL_SSL = 'https://wsr.brt.it:10052';
    public const BASE_URL = 'http://wsr.brt.it:10041';

    public function __construct($wsdl, $options = null)
    {
        $default = [
            'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
            'content-type' => 'application/xml',
        ];

        // Crea il contesto per la connessione SSL
        // In ambiente di produzione, è consigliabile mantenere le verifiche SSL attive
        // In ambiente di test o sviluppo, potrebbe essere necessario disabilitarle
        $verify_ssl = true;
        
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
            'soap_version' => SOAP_1_1,
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'encoding' => 'UTF-8',
            'exceptions' => true,
            'content-type' => 'application/xml',
        ];

        if ($options) {
            $options = array_merge($options, $default);
        } else {
            $options = $default;
        }

        try {
            parent::__construct($wsdl, $options);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();
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
                $this->errors[] = 'Chiamata a ' . $function . ' con parametri: ' . json_encode($params);
                $this->errors[] = 'Endpoint: ' . $this->__getLastRequestHeaders();
            }
            
            // Esegui la chiamata SOAP
            $response = $this->$function($params);

            // Converti la risposta in array
            $result = json_decode(json_encode($response), true);
            
            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                $this->errors[] = 'Risposta: ' . json_encode($result);
            }
            
            return $result;
        } catch (\SoapFault $e) {
            // Registra l'errore SOAP
            $error_msg = 'Errore SOAP: ' . $e->getMessage();
            $this->errors[] = $error_msg;
            
            // Aggiungi informazioni di debug se siamo in modalità sviluppo
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
                $this->errors[] = 'Ultima richiesta: ' . $this->__getLastRequest();
                $this->errors[] = 'Ultimo header richiesta: ' . $this->__getLastRequestHeaders();
                $this->errors[] = 'Ultima risposta: ' . $this->__getLastResponse();
                $this->errors[] = 'Ultimo header risposta: ' . $this->__getLastResponseHeaders();
            }
            
            return ['error' => $error_msg];
        } catch (\Exception $e) {
            // Registra altri errori
            $error_msg = 'Errore generico: ' . $e->getMessage();
            $this->errors[] = $error_msg;
            return ['error' => $error_msg];
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}