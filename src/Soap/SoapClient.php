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

class SoapClient
{
    protected $soapClient;
    protected $errors;

    public function __construct($wsdl)
    {
        $this->errors = [];
        $arrContextOptions = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                // Aumenta la compatibilità con server meno recenti
                'ciphers' => 'HIGH:!SSLv2:!SSLv3:!TLSv1.0',
            ],
            'http' => [
                'timeout' => 30,  // Timeout più lungo per le connessioni HTTP
            ]
        ]);

        $options = [
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

        try {
            $this->soapClient = new \SoapClient($wsdl, $options);
        } catch (\Throwable $th) {
            $this->soapClient = false;
            $this->errors[] = $th->getMessage();
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function exec(string $command, array $params = [], array &$output = null, int &$result_code = null): bool|string
    {
        if ($this->soapClient === false) {
            return false;
        }

        try {
            $output = $this->soapClient->__soapCall($command, $params);
            $result_code = 0;
            return true;
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();
            $result_code = 1;
            return false;
        }
    }
}