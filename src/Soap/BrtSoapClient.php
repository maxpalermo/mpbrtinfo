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

        $arrContextOptions = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ]]);
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

    public function exec($function, $params): array
    {
        try {
            $response = $this->$function($params);

            // Return response as Array
            return json_decode(json_encode($response), true);
        } catch (\SoapFault $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}