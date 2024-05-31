<?php
use MpSoft\MpBrtInfo\Ajax\AjaxInsertEsitiSOAP;
use MpSoft\MpBrtInfo\Ajax\AjaxInsertEventiSOAP;

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

use MpSoft\MpBrtInfo\Bolla\TemplateBolla;
use MpSoft\MpBrtInfo\Helpers\BrtOrder;
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

    const FETCH_LIMIT = 300;

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

        $sessionJSON = $this->getJsonFetch();
        if (isset($sessionJSON['action']) && isset($sessionJSON['ajax'])) {
            $action = 'displayAjax' . ucfirst($sessionJSON['action']);
            if (method_exists($this, $action)) {
                $this->$action($sessionJSON);
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
        $orders = $this->fetchTotalShippings();
        $response = $this->fetchShippingInfo($orders);

        $this->response($response);
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
        /** @var MpSoft\MpBrtInfo\Bolla\Bolla */
        $bolla = $class->getSoapTrackingByShipmentId($lang_iso, $spedizione_anno, $spedizione_id);

        if ($bolla === false) {
            return ['error' => $class->getErrors()];
        }

        return $bolla;
    }

    public function displayAjaxTrackingInfoByIdCollo()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $spedizione_anno = $sessionJSON['spedizione_anno'];
            $spedizione_id = $sessionJSON['spedizione_id'];
            $lang_iso = isset($sessionJSON['lang_iso']) ? $sessionJSON['lang_iso'] : '';
            $bolla = $this->TrackingInfoByIdCollo($lang_iso, $spedizione_anno, $spedizione_id);
            if (isset($bolla['error'])) {
                $this->response($bolla);
            }

            $this->response(['response' => $bolla]);
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

        if (!is_array($id_order_state_delivered)) {
            $id_order_state_delivered = [$id_order_state_delivered];
        }

        $orderHistory = BrtOrder::getOrdersHistoryIdExcludingOrderStates($id_order_state_delivered, self::FETCH_LIMIT);
        $response = $this->fetchOrders($orderHistory);
        $orders = BrtOrder::getOrdersIdExcludingOrderStates($id_order_state_delivered, $orderHistory, self::FETCH_LIMIT);
        $response = array_merge($response, $this->fetchOrders($orders));

        $this->response([
            'logs' => $response['logs'],
            'errors' => $response['errors'],
            'tot_logs' => count($response['logs']),
            'tot_errors' => count($response['errors']),
        ]);
    }

    protected function fetchOrders($orders)
    {
        $esiti = ModelBrtEsito::getEsiti();
        $errors = [];
        $logs = [];

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
                        $logs[] = $res;
                    }
                }
            } else {
                $logs[] = sprintf('ID ORDER: %s - TRACKING: %s - ESITO: %s', $id_order, $tracking, $esito);
            }
        }

        return [
            'logs' => $logs,
            'errors' => $errors,
        ];
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
        if (is_array($bolla) && isset($bolla['error'])) {
            $this->response(['errors' => $bolla['error']]);
        }
        $tpl = new TemplateBolla($bolla);

        $this->response(['content' => $tpl->display()]);
    }

    public function displayAjaxPostInfoBySpedizioneId($params)
    {
        $order_id = (int) $params['order_id'];
        $spedizione_id = (int) $params['spedizione_id'];

        $order = new Order($order_id);
        if (!Validate::isLoadedObject($order)) {
            $this->response(['content' => '<div id="BrtBolla" class="alert alert-danger">Order not found</div>']);
        }

        $anno = date('Y', strtotime($order->date_add));

        $bolla = $this->fetchInfoBySpedizioneId($anno, $spedizione_id);
        $tpl = new TemplateBolla($bolla);

        $this->response(['content' => $tpl->display()]);
    }

    public function displayAjaxInsertEventiSQL()
    {
    }

    public function displayAjaxInsertEsitiSQL()
    {
    }

    public function displayAjaxInsertEventiSOAP()
    {
        $class = new AjaxInsertEventiSOAP();
        $this->response(['eventi' => $class->insert()]);
    }

    public function displayAjaxInsertEsitiSOAP()
    {
        $class = new AjaxInsertEsitiSOAP();
        $this->response(['esiti' => $class->insert()]);
    }

    /*********************************************
     * FETCH METHODS - Nuovi metodi per il fetch *
     * ========================================= *
     *********************************************/

    protected function fetchTotalShippings()
    {
        $id_order_state_delivered = json_decode(Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED), true);

        if (!is_array($id_order_state_delivered)) {
            $id_order_state_delivered = [$id_order_state_delivered];
        }

        $orderHistory = BrtOrder::getOrdersHistoryIdExcludingOrderStates($id_order_state_delivered, self::FETCH_LIMIT);
        $orders = BrtOrder::getOrdersIdExcludingOrderStates($id_order_state_delivered, $orderHistory, self::FETCH_LIMIT);

        $totalShippings = array_merge($orders, $orderHistory);

        return $totalShippings;
    }

    public function displayAjaxFetchTotalShippings()
    {
        $totalShippings = $this->fetchTotalShippings();
        if (empty($totalShippings)) {
            $this->response([
                'status' => 'success',
                'total_shippings' => [],
                'message' => 'No shippings found',
            ]);
        }

        $this->response([
            'status' => 'success',
            'total_shippings' => $totalShippings,
        ]);
    }

    protected function fetchShippingInfo($orders)
    {
        $esiti = ModelBrtEsito::getEsiti();
        $processed = 0;
        $success = 0;
        $errors = [];
        $logs = [];
        $status = 'success';

        // Start TIMER
        $start_time = (int) microtime(true);

        foreach ($orders as $id_order) {
            ++$processed;

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
                        $logs[] = $res;
                        ++$success;
                    } else {
                        $logs[] = sprintf('ID ORDER: %s - TRACKING: %s - ESITO: %s: - ULTIMO EVENTO: (%s) %s %s. Stato ordine non cambiato.', $id_order, $tracking, $esito, $last_event->getData(), $last_event->getId(), $last_event->getDescrizione());
                    }
                }
            } else {
                $logs[] = sprintf('ID ORDER: %s - TRACKING: %s - ESITO: %s', $id_order, $tracking, $esito);
            }
        }

        $end_time = (int) microtime(true);
        $elapsed_time = $end_time - $start_time;
        $time = gmdate('H:i:s', (int) $elapsed_time);

        return [
            'status' => $status,
            'logs' => $logs,
            'errors' => $errors,
            'processed' => $processed,
            'order_changed' => $success,
            'elapsed_time' => $time,
        ];
    }

    public function displayAjaxFetchShippingInfo($params)
    {
        $orders = $params['shipments_id'] ?? [];

        if (empty($orders)) {
            $this->response(['status' => 'error', 'message' => 'No orders found']);
        }

        $response = $this->fetchShippingInfo($orders);

        $this->response($response);
    }

    public function displayAjaxExecQuery($params)
    {
        $query = $params['query'] ?? '';

        if (empty($query)) {
            $this->response(['status' => 'error', 'message' => 'Query not found']);
        }

        try {
            $db = Db::getInstance();
            if (preg_match('/^select/', trim($query))) {
                $res = $db->executeS($query);
            } else {
                $res = $db->execute($query);
            }
            if ($res) {
                $this->response(['status' => 'success', 'message' => 'Query executed successfully', 'query' => $query, 'rows_affected' => $db->Affected_Rows(), 'result' => $res]);
            } else {
                $this->response(['status' => 'error', 'message' => 'Query not executed', 'query' => $query, 'error' => $db->getMsgError()]);
            }
        } catch (\Throwable $th) {
            $this->response(['status' => 'error', 'message' => 'Query not executed', 'query' => $query, 'error' => $th->getMessage()]);
        }
    }

    public function displayAjaxSetShippedDate()
    {
        $db = Db::getInstance();
        $sql = 'SELECT distinct id_order FROM ' . _DB_PREFIX_ . 'mpbrtinfo_tracking_number WHERE date_shipped IS NULL or date_shipped = "0000-00-00 00:00:00"';
        $results = $db->executeS($sql);

        if ($results) {
            $orders = array_column($results, 'id_order');
        } else {
            $orders = [];
        }

        foreach ($orders as $id_order) {
            $date_shipped = ModelBrtTrackingNumber::getDateShipped($id_order);

            if ($date_shipped) {
                $anno_spedizione = date('Y', strtotime($date_shipped));
                $sql = 'UPDATE '
                    . _DB_PREFIX_ . 'mpbrtinfo_tracking_number '
                    . 'SET date_shipped = "' . $date_shipped . ', '
                    . 'anno_spedizione = ' . (int) $anno_spedizione
                    . '" WHERE id_order = ' . (int) $id_order;
                $result = $db->execute($sql);
            }
        }

        $this->response(['status' => 'success', 'message' => 'Shipped date updated']);
    }

    public function displayAjaxSetDeliveredDays()
    {
        $timer = (int) microtime(true);

        $delivered_state = ModelBrtConfig::get(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
        if (is_array($delivered_state)) {
            $delivered_state = array_map('intval', $delivered_state);
            $delivered_state = implode(',', $delivered_state);
        }

        $db = Db::getInstance();
        $sql = 'SELECT id_mpbrtinfo_tracking_number, date_delivered, date_add '
            . 'FROM ' . _DB_PREFIX_ . 'mpbrtinfo_tracking_number '
            . 'WHERE id_order_state IN (' . $delivered_state . ') '
            . 'AND (date_delivered IS NOT NULL OR date_delivered != "0000-00-00 00:00:00")';
        $results = $db->executeS($sql);

        foreach ($results as $result) {
            ModelBrtTrackingNumber::setDeliveredDays($result['id_mpbrtinfo_tracking_number'], $result['date_delivered'], $result['date_add']);
        }

        $timer_ends = (int) microtime(true);
        $elapsed = $timer_ends - $timer;
        $time = gmdate('H:i:s', (int) $elapsed);

        $this->response(['status' => 'success', 'message' => 'Delivered date updated', 'affected_rows' => count($results), 'elapsed_time' => $time]);
    }

    public function displayAjaxUpdateDeliveredDate()
    {
        $timer = (int) microtime(true);

        $brt_delivered = ModelBrtConfig::get(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
        if (!is_array($brt_delivered)) {
            $brt_delivered = [(int) $brt_delivered];
        }

        $db = Db::getInstance();
        $sql = 'SELECT id_mpbrtinfo_tracking_number, id_collo, rmn '
            . 'FROM ' . _DB_PREFIX_ . 'mpbrtinfo_tracking_number '
            . 'WHERE days > 10 '
            . 'AND id_order_state IN (' . implode(',', $brt_delivered) . ') ';
        $results = $db->executeS($sql);

        foreach ($results as $result) {
            if ($result['id_collo']) {
                $id_collo = $result['id_collo'];
            } elseif ($result['rmn']) {
                $id_collo = $this->getIdSpedizioneByRMN('', $result['rmn']);
                if (!$id_collo) {
                    continue;
                }
            } else {
                continue;
            }

            /** @var MpSoft\MpBrtInfo\Bolla\Bolla */
            $bolla = $this->TrackingInfoByIdCollo('', date('Y'), $id_collo);
            if ($bolla && $bolla->getEsito() == 0) {
                $last_event = $bolla->getLastEvento();
                if ($last_event) {
                    $date_ita = explode('.', $last_event->getData());
                    $hour = str_replace('.', ':', $last_event->getOra()) . ':00';
                    $date_delivered = $date_ita[2] . '-' . $date_ita[1] . '-' . $date_ita[0] . ' ' . $hour;

                    $table = _DB_PREFIX_ . 'mpbrtinfo_tracking_number';
                    $id = (int) $result['id_mpbrtinfo_tracking_number'];
                    $sql = "UPDATE {$table} SET `date_delivered` = '{$date_delivered}' WHERE `id_mpbrtinfo_tracking_number` = {$id}";
                    $db->execute($sql);
                }
            }
        }

        if ($results) {
            $this->displayAjaxSetDeliveredDays();
        }

        $timer_ends = (int) microtime(true);
        $elapsed = $timer_ends - $timer;
        $time = gmdate('H:i:s', (int) $elapsed);

        $this->response(['status' => 'success', 'message' => 'No order needs to be updated', 'affected_rows' => count($results), 'elapsed_time' => $time]);
    }
}
