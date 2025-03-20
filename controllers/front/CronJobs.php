<?php

use MpSoft\MpBrtInfo\Ajax\AjaxInsertEsitiSOAP;
use MpSoft\MpBrtInfo\Ajax\AjaxInsertEventiSOAP;
use MpSoft\MpBrtInfo\Bolla\TemplateBolla;
use MpSoft\MpBrtInfo\Helpers\BrtOrder;

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

use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientTrackingByShipmentId;
use MpSoft\MpBrtInfo\Soap\BrtSoapEsiti;
use MpSoft\MpBrtInfo\Soap\GetIdSpedizioneByIdCollo;
use MpSoft\MpBrtInfo\Soap\GetIdSpedizioneByRMA;
use MpSoft\MpBrtInfo\Soap\GetIdSpedizioneByRMN;
use MpSoft\MpBrtInfo\WSDL\GetLegendaEventi;

class MpBrtInfoCronJobsModuleFrontController extends ModuleFrontController
{
    /** @var string The name of the controller */
    public $name;
    protected $esiti;

    const FETCH_LIMIT = 500;

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
        $client = new BrtSoapEsiti();
        $risultati = $client->getLegendaEsiti('it', 0);

        if ($risultati === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            return $risultati;
        }
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
        $client = new GetLegendaEventi();
        $risultati = $client->getLegendaEventi('it', '');

        if ($risultati === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            return $risultati;
        }
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
        $year = date('Y');
        $class = new GetIdSpedizioneByRMN($rmn, $year, $brt_customer_id);
        $esiti = $class->getIdSpedizione();

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
        $year = date('Y');
        $class = new GetIdSpedizioneByRMA($rma, $year, $brt_customer_id);
        $esiti = $class->getIdSpedizione();

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
        $class = new GetIdSpedizioneByIdCollo($collo_id, $brt_customer_id);
        $esiti = $class->getIdSpedizione();

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
        $json = $this->getJsonFetch();
        $eventi = $json['eventi'];
        $db = Db::getInstance();
        $event_list = [];
        $errors = [];
        $updated = 0;

        foreach ($eventi as $evento) {
            if (!isset($evento['id'])) {
                continue;
            }
            if (!isset($evento['name'])) {
                continue;
            }
            $id = $evento['id'];
            $name = $evento['name'];
            $checked = (int) $evento['checked'];

            $event_list[$id][] = [
                'name' => $name,
                'checked' => $checked,
            ];
        }

        foreach ($event_list as $id => $events) {
            $sql = 'UPDATE ' . _DB_PREFIX_ . 'mpbrtinfo_evento SET '
                . 'is_error = 0, is_transit = 0, is_delivered = 0, is_fermopoint = 0, is_waiting = 0, is_refused = 0, is_sent=0 '
                . 'WHERE id_evento = ' . (int) $id;
            $db->execute($sql);

            foreach ($events as $event) {
                $sql = 'UPDATE ' . _DB_PREFIX_ . 'mpbrtinfo_evento SET ' . $event['name'] . ' = 1 '
                    . 'WHERE id_mpbrtinfo_evento = ' . (int) $id;

                try {
                    $db->execute($sql);
                    $updated++;
                } catch (\Throwable $th) {
                    $errors[] = $th->getMessage();
                }
            }
        }

        $this->response(['updated' => $updated, 'errors' => $errors]);
    }

    public function displayAjaxUpdateEventi2()
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

    public function fetchInfoBySpedizioneId(int $anno, string $tracking_number)
    {
        $client = new BrtSoapClientTrackingByShipmentId();
        $info = $client->getSoapTrackingByShipmentId('', $anno, $tracking_number);
        if ($info === false) {
            return ['error' => $client->getErrors()];
        }

        return $info;
    }

    public function displayAjaxFetchInfoBySpedizioneId()
    {
        $fetch = $this->getJsonFetch();

        $anno = $fetch['spedizione_anno'] ?? date('Y');
        $spedizione_id = str_pad($fetch['spedizione_id'], 12, '0');
        // if (strlen($spedizione_id) != 12) {
        //    $this->response(['errors' => ['Spedizione ID non valido.']]);
        // }

        $bolla = $this->fetchInfoBySpedizioneId($anno, $spedizione_id);
        if (is_array($bolla) && isset($bolla['error'])) {
            $this->response(['errors' => $bolla['error']]);
        }
        if ($bolla->getEsito() != 0) {
            $this->response(['errors' => [sprintf('(%s) %s', $bolla->getEsito(), $bolla->getEsitoDesc())]]);
        }

        $tpl = new TemplateBolla($bolla);

        $this->response(['content' => $tpl->display()]);
    }

    public function displayAjaxPostInfoBySpedizioneId($params)
    {
        $order_id = (int) $params['order_id'];
        $spedizione_id = str_pad($params['spedizione_id'], 12, '0');

        if (strlen($spedizione_id) != 12) {
            $spedizione_id = ModelBrtHistory::getIdColloByIdOrder($order_id);

            if (isset($spedizione_id['tracking_number'])) {
                $spedizione_id = $spedizione_id['tracking_number'];
            } else {
                $spedizione_id = '';
            }
        }

        $order = new Order($order_id);
        if (!Validate::isLoadedObject($order)) {
            $this->response([
                'content' => [
                    'error' => true,
                    'error_code' => -99,
                    'message' => sprintf('Ordine %s non trovato.', $order_id),
                ],
            ]);
        }

        if (!$spedizione_id) {
            $spedizione_id = ModelBrtHistory::getIdColloByIdOrder($order_id);

            if (isset($spedizione_id['tracking_number'])) {
                $spedizione_id = $spedizione_id['tracking_number'];
            } else {
                $spedizione_id = '';
            }
        }

        if (!$spedizione_id) {
            $this->response([
                'content' => [
                    'error' => true,
                    'error_code' => -98,
                    'message' => sprintf('Id Spedizione per l\'Ordine %s non trovato.', $order_id),
                ],
            ]);
        }

        $anno_spedizione = ModelBrtHistory::getAnnoSpedizione($order_id);

        $bolla = $this->fetchInfoBySpedizioneId($anno_spedizione, $spedizione_id);
        $tpl = new TemplateBolla($bolla);

        $this->response(['content' => $tpl->display(), 'tracking' => $spedizione_id]);
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
        $id_order_state_sent = json_decode(Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_SENT), true);
        $id_order_state_skip = json_decode(Configuration::get(ModelBrtConfig::MP_BRT_INFO_OS_SKIP), true);

        if (!is_array($id_order_state_delivered)) {
            $id_order_state_delivered = [$id_order_state_delivered];
        }
        if (!is_array($id_order_state_sent)) {
            $id_order_state_sent = [$id_order_state_sent];
        }
        if (!is_array($id_order_state_skip)) {
            $id_order_state_skip = [$id_order_state_skip];
        }

        BrtOrder::checkDelivered($id_order_state_delivered);

        $orderHistory = BrtOrder::getOrdersHistoryIdExcludingOrderStates($id_order_state_skip, self::FETCH_LIMIT);
        $orders = BrtOrder::getOrdersIdExcludingOrderStates($id_order_state_skip, $orderHistory, self::FETCH_LIMIT);

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

    public function displayAjaxSetDeliveredDateFromOrderCarrier()
    {
        $errors = [];
        $carriers = ModelBrtConfig::getCarriers();
        $id_carriers = implode(',', $carriers);
        $delivered_state = json_decode(ModelBrtConfig::get(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED));
        if (!is_array($delivered_state)) {
            $delivered_state = [$delivered_state];
        }
        $inserted = 0;

        $db = Db::getInstance();

        $sql_delivered = 'SELECT id_evento FROM ' . _DB_PREFIX_ . "mpbrtinfo_evento WHERE name = 'CONSEGNATA'";
        $id_delivered = $db->getValue($sql_delivered);
        if (!$id_delivered) {
            $id_delivered = 704;
        }

        $sql = 'SELECT tn.*, oh.date_add as oh_date_delivered FROM '
            . _DB_PREFIX_ . 'mpbrtinfo_tracking_number tn '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'order_history oh ON (tn.id_order = oh.id_order AND oh.id_order_state IN (' . implode(',', $delivered_state) . ')) '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier oc ON (tn.id_order = oc.id_order) '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (tn.id_order = o.id_order) '
            . "WHERE o.id_carrier IN ({$id_carriers}) AND oh.id_order IS NOT NULL "
            . 'AND o.id_order not in (SELECT distinct id_order FROM ' . _DB_PREFIX_ . 'mpbrtinfo_tracking_number WHERE current_state = \'DELIVERED\') '
            . 'ORDER BY oh.date_add ASC';
        $rows = $db->executeS($sql);

        $date_shipped = [];
        if ($rows) {
            foreach ($rows as &$row) {
                unset($row['id_mpbrtinfo_tracking_number']);
                $date_shipped = $row['date_event'];
                $date_delivered = $row['oh_date_delivered'];
                $days = ModelBrtHistory::countDays($date_shipped, $date_delivered);

                $model = new ModelBrtHistory();
                $model->hydrate($row);
                $model->date_event = $date_delivered;
                $model->date_delivered = $date_delivered;
                $model->id_brt_state = $id_delivered;
                $model->current_state = 'DELIVERED';
                $model->days = $days;

                try {
                    $model->save(true);
                    ++$inserted;
                } catch (\Throwable $th) {
                    $errors[] = sprintf('Ordine %s: Errore %s', $model->id_order, $th->getMessage());
                }
            }
        }

        $this->response(['status' => 'success', 'message' => sprintf('Inseriti %d ordini allo stato CONSEGNATO', $inserted), 'errors' => $errors]);
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
            ModelBrtHistory::setDeliveredDays($result['id_mpbrtinfo_tracking_number'], $result['date_delivered'], $result['date_add']);
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

    /**
     * Recupera i dettagli della spedizione BRT e li restituisce in formato JSON
     * Questa funzione è chiamata via AJAX dal template SweetAlert2
     */
    public function displayAjaxGetBrtShipmentDetails()
    {
        // Verifica che i parametri necessari siano presenti
        if (!Tools::isSubmit('id_order') || !Tools::isSubmit('tracking_number')) {
            $this->response(
                [
                    'success' => false,
                    'message' => 'Parametri mancanti: id_order e tracking_number sono richiesti',
                ]
            );

            return;
        }

        $id_order = (int) Tools::getValue('id_order');
        $tracking_number = pSQL(Tools::getValue('tracking_number'));
        $year_shipped = (new GetOrderShippingDate($id_order))->getShippingYear();
        if (!$year_shipped) {
            $year_shipped = date('Y');
        }

        // Verifica che l'ordine esista
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            $this->response(
                [
                    'success' => false,
                    'message' => 'Ordine non trovato',
                ]
            );

            return;
        }

        try {
            // Recupera i dati della spedizione tramite SOAP
            $shipmentData = $this->getShipmentData($tracking_number, $id_order, $year_shipped);
            if (!isset($shipmentData['rmn'])) {
                return [
                    'success' => false,
                    'message' => implode('<br>', $shipmentData),
                ];
            }
            $this->prepareHistory($id_order, $year_shipped, $shipmentData);

            $events = [];
            if ($shipmentData['eventi']) {
                $eventi = $shipmentData['eventi'];
                foreach ($eventi as $evento) {
                    if ($evento->isDelivered()) {
                        $shipmentData['days'] = $this->countDays($id_order);
                    }
                    $events[] = [
                        'color' => $evento->getColor(),
                        'icon' => $evento->getIcon(),
                        'id' => $evento->getId(),
                        'data' => $evento->getData(),
                        'ora' => $evento->getOra(),
                        'descrizione' => $evento->getDescrizione(),
                        'filiale' => $evento->getFiliale(),
                        'label' => $evento->getLabel(),
                    ];
                }
            }
            // Prepara la risposta
            $response = [
                'success' => true,
                'data' => [
                    'id_order' => $id_order,
                    'tracking_number' => $tracking_number,
                    'data_spedizione' => $shipmentData['data_spedizione'] ?? date('d/m/Y'),
                    'porto' => $shipmentData['porto'] ?? 'Franco',
                    'servizio' => $shipmentData['servizio'] ?? 'Standard',
                    'colli' => $shipmentData['colli'] ?? 1,
                    'peso' => $shipmentData['peso'] ?? '0 Kg',
                    'natura' => $shipmentData['natura'] ?? 'Merce',
                    'stato_attuale' => $this->getCurrentStatus($tracking_number, $id_order),
                    'storico' => $events,
                    'days' => $shipmentData['days'] ?? '--',
                ],
            ];

            $this->response($response);
        } catch (Exception $e) {
            $this->response(
                [
                    'success' => false,
                    'message' => 'Errore durante il recupero dei dati della spedizione: ' . $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Imposta un ordine come consegnato
     * Questa funzione è chiamata via AJAX dal template SweetAlert2
     */
    public function displayAjaxSetOrderAsDelivered()
    {
        // Verifica che i parametri necessari siano presenti
        if (!Tools::isSubmit('id_order')) {
            $this->response(
                [
                    'success' => false,
                    'message' => 'Parametro mancante: id_order è richiesto',
                ]
            );

            return;
        }

        $id_order = (int) Tools::getValue('id_order');

        // Verifica che l'ordine esista
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            $this->response(
                [
                    'success' => false,
                    'message' => 'Ordine non trovato',
                ]
            );

            return;
        }

        try {
            // Recupera lo stato "Consegnato" dalla configurazione
            $id_order_state_delivered = (int) \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);

            if (!$id_order_state_delivered) {
                // Se non è configurato, usa lo stato di default di PrestaShop per "Consegnato"
                $id_order_state_delivered = (int) Configuration::get('PS_OS_DELIVERED');
            }

            // Aggiorna lo stato dell'ordine
            $history = new OrderHistory();
            $history->id_order = $id_order;
            $history->changeIdOrderState($id_order_state_delivered, $id_order);
            $history->addWithemail();

            // Aggiorna la data di consegna
            $order->delivery_date = date('Y-m-d H:i:s');
            $order->update();

            // Registra l'azione nel log
            PrestaShopLogger::addLog(
                'Ordine #' . $id_order . ' impostato come consegnato manualmente',
                1,
                null,
                'Order',
                $id_order,
                true
            );

            $this->response(
                [
                    'success' => true,
                    'message' => 'Ordine #' . $id_order . ' impostato come consegnato con successo',
                ]
            );
        } catch (Exception $e) {
            $this->response(
                [
                    'success' => false,
                    'message' => 'Errore durante l\'impostazione dell\'ordine come consegnato: ' . $e->getMessage(),
                ]
            );
        }
    }
}
