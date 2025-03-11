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

require_once _PS_MODULE_DIR_ . 'mpbrtinfo/models/autoload.php';

class BrtSoapShipmentId extends BrtSoapClient
{
    protected $id;
    protected $type;
    protected $where;
    /** @var int */
    protected $brt_customer_id;
    /** @var \stdClass */
    protected $request;
    /** @var string */
    protected $action;

    public function __construct($search_type = null, $search_where = null, $id_order = null, $shipment_id = null)
    {
        $url = '';
        $ssl = \ModelBrtConfig::useSSL();

        $this->brt_customer_id = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER);
        if (!$search_type) {
            $search_type = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE);
        }
        if (!$search_where) {
            $search_where = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE);
        }

        if ($search_where == \ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE_ID) {
            $search_field = 'id';
        } else {
            $search_field = 'reference';
        }

        $this->type = $search_type;
        $this->where = $search_type;
        $this->request = new \stdClass();
        $order = new \Order($id_order);
        $this->id = $order->{$search_field};

        switch ($search_type) {
            case 'RMN':
                if ($ssl) {
                    $url = 'https://wsr.brt.it:10052/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl';
                } else {
                    $url = 'http://wsr.brt.it:10041/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl';
                }
                $this->request->RIFERIMENTO_MITTENTE_NUMERICO = $this->id;
                $this->request->CLIENTE_ID = $this->brt_customer_id;
                $this->action = 'getidspedizionebyrmn';

                break;
            case 'RMA':
                if ($ssl) {
                    $url = 'https://wsr.brt.it:10052/web/GetIdSpedizioneByRMAService/GetIdSpedizioneByRMA?wsdl';
                } else {
                    $url = 'http://wsr.brt.it:10041/web/GetIdSpedizioneByRMAService/GetIdSpedizioneByRMA?wsdl';
                }
                $this->request->RIFERIMENTO_MITTENTE_ALFABETICO = $this->id;
                $this->request->CLIENTE_ID = $this->brt_customer_id;
                $this->action = 'getidspedizionebyrma';

                break;
            case 'ID':
            default:
                if ($ssl) {
                    $url = 'https://wsr.brt.it:10052/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdColloN?wsdl';
                } else {
                    $url = 'http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl';
                }
                $this->request->COLLO_ID = $this->id;
                $this->request->CLIENTE_ID = $this->brt_customer_id;
                $this->action = 'getidspedizionebyidcollo';
        }

        parent::__construct($url);
    }

    public function createRequest($id_brt_customer = null, $shipment_id = null)
    {
        if ($id_brt_customer) {
            $this->request->CLIENTE_ID = $this->brt_customer_id;
        }
        if ($shipment_id) {
            switch ($this->type) {
                case 'RMN':
                    $this->request->RIFERIMENTO_MITTENTE_NUMERICO = $shipment_id;

                    break;
                case 'RMA':
                    $this->request->RIFERIMENTO_MITTENTE_ALFABETICO = $shipment_id;

                    break;
                case 'ID':
                default:
                    $this->request->COLLO_ID = $shipment_id;
            }
        }

        return $this->request;
    }

    public function getShipmentId()
    {
        $request = $this->createRequest();
        try {
            $response = $this->exec($this->action, ['arg0' => $request]);
            if (isset($response['return'])) {
                return $response['return'];
            }
        } catch (\Throwable $th) {
            $this->errors[] = 'getShipmentId: request -> ' . print_r($request, 1);
            $this->errors[] = 'getShipmentId: error -> ' . $th->getMessage();
            return false;
        }

        return [];
    }
}
