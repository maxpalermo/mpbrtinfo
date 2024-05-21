<?php
use MpSoft\MpBrtInfo\Bolla\TemplateBolla;

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
if (!defined('_PS_VERSION_')) {
    exit;
}

use MpSoft\MpBrtInfo\Brt\BrtGetSoapTracking;
use MpSoft\MpBrtInfo\Helpers\BrtOrder;
use MpSoft\MpBrtInfo\Helpers\BrtParseInfo;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientEsiti;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientEventi;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByIdCollo;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByRMA;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByRMN;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientTrackingByShipmentId;

class MpBrtInfoCronJobsModuleFrontController extends ModuleFrontController
{
    /** @var string The name of the controller */
    public $name;
    protected $esiti;

    protected function getJsonFetch()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        return $sessionJSON;
    }

    public function response($params)
    {
        header('Content-Type: application/json');
        exit(json_encode($params));
    }

    public function __construct()
    {
        $this->name = 'CronJobs';
        $this->ajax = true;
        $this->auth = false;
        $this->guestAllowed = false;
        $this->ssl = (int) Configuration::get('PS_SSL_ENABLED');

        parent::__construct();

        $this->esiti = \ModelBrtConfig::getEsiti();

        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);
        if (isset($sessionJSON['action']) && isset($sessionJSON['ajax'])) {
            $action = 'displayAjax' . ucfirst($sessionJSON['action']);
            if (method_exists($this, $action)) {
                $this->$action();
                exit;
            }
        }
    }

    public function display()
    {
        if (Tools::isSubmit('action')) {
            $action = 'displayAjax' . Tools::ucfirst(Tools::getValue('action'));
            if (method_exists($this, $action)) {
                $this->$action();
                exit;
            }
        }

        $this->response('ACCESS DENIED');
    }

    public function displayAjax()
    {
        $this->ajaxRender('NO METHOD FOUND');
    }

    public function displayAjaxGetShippingInfo()
    {
        $soapTracking = new BrtGetSoapTracking($this->module);
        $trackings = $soapTracking->get();

        Tools::dieObject($trackings, false);
    }

    protected function getLegendaEsitiFromDb()
    {
        $sql = 'SELECT id_esito,testo1,testo2 FROM ' . _DB_PREFIX_ . 'mpbrtinfo_esito ORDER BY id_esito ASC';
        $db = Db::getInstance();
        $results = $db->executeS($sql);

        if ($results) {
            return $results;
        }

        return [];
    }

    protected function getLegendaEsiti()
    {
        $class = new BrtSoapClientEsiti();
        $esiti = $class->getSoapLegendaEsiti();

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetLegendaEsiti()
    {
        $esiti = $this->getLegendaEsiti();
        if (isset($esiti['error'])) {
            $this->response($esiti);
        }
        $this->response(['esiti' => $esiti]);
    }

    public function getLegendaEventi()
    {
        $class = new BrtSoapClientEventi();
        $esiti = $class->getSoapLegendaEventi();

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetLegendaEventi()
    {
        $esiti = $this->getLegendaEventi();
        if (isset($esiti['error'])) {
            $this->response($esiti);
        }
        $this->response(['eventi' => $esiti]);
    }

    public function getIdSpedizioneByRMN($brt_customer_id, $rmn)
    {
        $class = new BrtSoapClientIdSpedizioneByRMN();
        $esiti = $class->getSoapIdSpedizioneByRMN($brt_customer_id, $rmn);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetIdSpedizioneByRMN()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $brt_customer_id = $sessionJSON['brt_customer_id'];
            $rmn = $sessionJSON['brt_rmn'];
            $esiti = $this->getIdSpedizioneByRMN($brt_customer_id, $rmn);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }
            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function getIdSpedizioneByRMA($brt_customer_id, $rma)
    {
        $class = new BrtSoapClientIdSpedizioneByRMA();
        $esiti = $class->getSoapIdSpedizioneByRMA($brt_customer_id, $rma);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetIdSpedizioneByRMA()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $brt_customer_id = $sessionJSON['brt_customer_id'];
            $rma = $sessionJSON['brt_rma'];
            $esiti = $this->getIdSpedizioneByRMA($brt_customer_id, $rma);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }
            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function getIdSpedizioneByIdCollo($brt_customer_id, $collo_id)
    {
        $class = new BrtSoapClientIdSpedizioneByIdCollo();
        $esiti = $class->getSoapIdSpedizioneByIdCollo($brt_customer_id, $collo_id);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetIdSpedizioneByIdCollo()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $brt_customer_id = $sessionJSON['brt_customer_id'];
            $collo_id = $sessionJSON['collo_id'];
            $esiti = $this->getIdSpedizioneByIdCollo($brt_customer_id, $collo_id);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }
            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function TrackingInfoByIdCollo($lang_iso, $spedizione_anno, $spedizione_id)
    {
        $class = new BrtSoapClientTrackingByShipmentId();
        $esiti = $class->getSoapTrackingByShipmentId($lang_iso, $spedizione_anno, $spedizione_id);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        $infoTracking = BrtParseInfo::parseTrackingInfo($esiti, $this->esiti);

        return $esiti;
    }

    public function displayAjaxTrackingInfoByIdCollo()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $spedizione_anno = $sessionJSON['spedizione_anno'];
            $spedizione_id = $sessionJSON['spedizione_id'];
            $lang_iso = isset($sessionJSON['lang_iso']) ? $sessionJSON['lang_iso'] : '';
            $esiti = $this->TrackingInfoByIdCollo($lang_iso, $spedizione_anno, $spedizione_id);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }

            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function displayAjaxUpdateEventi()
    {
        $eventi = $this->getLegendaEventi();
        $exists = 'SELECT id_evento FROM ' . _DB_PREFIX_ . 'mpbrtinfo_evento ORDER BY id_evento ASC';
        $db = Db::getInstance();
        $results = $db->executeS($exists);
        $updated = [];
        $errors = [];

        if ($results) {
            $results = array_column($results, 'id_evento');
        }
        foreach ($eventi as $evento) {
            if (!in_array($evento['ID'], $results)) {
                $insert = 'INSERT IGNORE INTO ' . _DB_PREFIX_ . "mpbrtinfo_evento (id_evento, name, date_add) VALUES ('" . $evento['ID'] . "', '" . pSQL($evento['DESCRIZIONE']) . "', '" . date('Y-m-d H:i:s') . "');";

                try {
                    $res = $db->execute($insert);
                    if ($res) {
                        $updated[] = $evento;
                    } else {
                        $errors[] = ['ID' => $evento['ID'], 'DESCRIZIONE' => $evento['DESCRIZIONE'], 'error' => $db->getMsgError()];
                    }
                } catch (\Throwable $th) {
                    $this->response(['error' => $th->getMessage()]);
                }
            }
        }

        $this->response(['updated' => $updated, 'errors' => $errors]);
    }

    public function displayAjaxFetchTracking()
    {
        $id_order_states = json_decode(Configuration::get(ModelBrtConfig::MP_BRT_INFO_OS_CHECK_FOR_TRACKING), true);
        $fetch_type = Configuration::get(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE);
        $fetch_where = Configuration::get(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE);
        $brt_customer_id = Configuration::get(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER);

        $errors = [];
        $trackings = [];

        if ($fetch_where == 'ID') {
            $orders = BrtOrder::getOrdersIdByIdOrderStates($id_order_states, 50);
        } else {
            $orders = BrtOrder::getOrdersReferenceByIdOrderStates($id_order_states, 50);
        }

        if ($fetch_type == 'RMN') {
            foreach ($orders as $id_order) {
                $client = new BrtSoapClientIdSpedizioneByRMN();
                $tracking = $client->getSoapIdSpedizioneByRMN($brt_customer_id, $id_order);
                $esito = $this->esiti[$tracking['esito']];
                if ($tracking['esito'] == 0) {
                    $trackings[] = ['id_order' => $id_order, 'tracking' => $tracking['spedizione_id'], 'esito' => $esito];
                } else {
                    $errors[] = ['id_order' => $id_order, 'tracking' => $tracking['spedizione_id'], 'esito' => $esito];
                }
            }
        } elseif ($fetch_type == 'RMA') {
            foreach ($orders as $id_order) {
                $client = new BrtSoapClientIdSpedizioneByRMA();
                $tracking = $client->getSoapIdSpedizioneByRMA($brt_customer_id, $id_order);
            }
        } elseif ($fetch_type == 'ID') {
            foreach ($orders as $id_order) {
                $client = new BrtSoapClientIdSpedizioneByIdCollo();
                $tracking = $client->getSoapIdSpedizioneByIdCollo($brt_customer_id, $id_order);
            }
        }

        $this->response(['trackings' => $trackings, 'errors' => $errors]);
    }

    public function displayAjaxFetchInfo()
    {
        $id_order_state_delivered = json_decode(Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED), true);
        $log = '';
        $errors = [];
        $esiti = ModelBrtEsito::getEsiti();

        if (!is_array($id_order_state_delivered)) {
            $id_order_state_delivered = [$id_order_state_delivered];
        }

        $orders = BrtOrder::getOrdersIdExcludingOrderStates($id_order_state_delivered, 250);
        foreach ($orders as $id_order) {
            $tracking = ModelBrtTrackingNumber::getIdColloByIdOrder($id_order);
            if (!$tracking) {
                // Provo a cercare il tracking online
                $search_type = Configuration::get(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE);
                $search_where = Configuration::get(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE);
                $id_brt_customer = Configuration::get(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER);

                if ($search_type == 'RMN') {
                    $client = new BrtSoapClientIdSpedizioneByRMN();
                    if ($search_where == 'ID') {
                        $esito = $client->getSoapIdSpedizioneByRMN($id_brt_customer, $id_order);
                    } else {
                        $order = new Order($id_order);
                        $esito = $client->getSoapIdSpedizioneByRMN($id_brt_customer, $order->reference);
                    }
                } elseif ($search_type == 'RMA') {
                    $client = new BrtSoapClientIdSpedizioneByRMA();
                    if ($search_where == 'ID') {
                        $esito = $client->getSoapIdSpedizioneByRMA($id_brt_customer, $id_order);
                    } else {
                        $order = new Order($id_order);
                        $esito = $client->getSoapIdSpedizioneByRMA($id_brt_customer, $order->reference);
                    }
                }

                if ($esito['esito'] == 0) {
                    $tracking = $esito['spedizione_id'];
                    ModelBrtTrackingNumber::setAsSent($id_order, $tracking);
                } else {
                    $tracking = '';
                    $esito = $esiti[$esito['esito']];
                    $errors[] = ['id_order' => $id_order, 'info' => $esito, 'esito' => 'NO TRACKING'];
                    unset ($esito);

                    continue;
                }
            }
            $client = new BrtSoapClientTrackingByShipmentId();
            $info = $client->getSoapTrackingByShipmentId('', date('Y'), $tracking);
            if ($info === false) {
                $errors[] = ['id_order' => $id_order, 'info' => $tracking, 'esito' => 'NO INFO'];

                continue;
            }

            $id_esito = $info->getEsito();
            $esito = $this->esiti[$id_esito];

            if ($id_esito == 0) {
                $last_event = $info->getLastEvento();
                $rmn = $info->getRiferimenti()->getRiferimentoMittenteNumerico();
                $id_collo = $info->getDatiSpedizione()->getSpedizioneId();
                if ($last_event) {
                    $res = $info::changeIdOrderState($id_order, $last_event, $rmn, $id_collo);
                    if ($res) {
                        $log .= $res . "\n";
                    }
                }
            } else {
                $log .= sprintf('ID ORDER: %s - TRACKING: %s - ESITO: %s', $id_order, $tracking, $esito) . "\n";
            }
        }

        $this->response(['log' => $log, 'errors' => implode("\n", $errors)]);
    }

    public function fetchInfoBySpedizioneId($anno, $spedizione_id)
    {
        $client = new BrtSoapClientTrackingByShipmentId();
        $info = $client->getSoapTrackingByShipmentId('', $anno, $spedizione_id);
        if ($info === false) {
            return ['error' => $client->getErrors()];
        }

        return $info;
    }

    public function displayAjaxFetchInfoBySpedizioneId()
    {
        $fetch = $this->getJsonFetch();

        $anno = $fetch['spedizione_anno'] ?? date('Y');
        $spedizione_id = $fetch['spedizione_id'];

        $bolla = $this->fetchInfoBySpedizioneId($anno, $spedizione_id);
        $tpl = new TemplateBolla($bolla);

        $this->response(['content' => $tpl->display()]);
    }

    public function displayAjaxPostInfoBySpedizioneId()
    {
        $order_id = Tools::getValue('order_id', 0);
        $spedizione_id = Tools::getValue('spedizione_id');

        $order = new Order($order_id);
        if (!Validate::isLoadedObject($order)) {
            $this->response(['content' => '<div id="BrtBolla" class="alert alert-danger">Order not found</div>']);
        }

        $anno = date('Y', strtotime($order->date_add));

        $bolla = $this->fetchInfoBySpedizioneId($anno, $spedizione_id);
        $tpl = new TemplateBolla($bolla);

        $this->response(['content' => $tpl->display()]);
    }
}
