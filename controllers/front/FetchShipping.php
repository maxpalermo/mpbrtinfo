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
if (!defined('_PS_VERSION_')) {
    exit;
}

use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\Response\Response;
use MpSoft\MpBrtInfo\Soap\TrackingInfo;
use MpSoft\MpBrtInfo\Soap\TrackingNumber;

class MpBrtInfoFetchShippingModuleFrontController extends ModuleFrontController
{
    protected $post_json;

    public function __construct()
    {
        $data = file_get_contents('php://input');
        if ($data) {
            $this->post_json = json_decode($data, true);
        }

        parent::__construct();

        if (isset($this->post_json['action']) && isset($this->post_json['ajax'])) {
            $action = 'ajax' . Tools::ucfirst($this->post_json['action']);
            if (method_exists($this, $action)) {
                Response::json($this->$action($this->post_json));
                exit;
            }
        }
    }

    public function ajaxFetchTotalShippings($post_json)
    {
        $id_carriers = ModelBrtConfig::getCarriers();
        $id_delivered = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED, 0);
        $id_state_skip = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_OS_SKIP, []);
        $max_date = date('Y-m-d', strtotime('-30 days'));

        if (!$id_carriers) {
            return [
                'success' => false,
                'message' => 'Nessun carrier configurato',
            ];
        }

        if ($id_carriers) {
            if (!is_array($id_carriers)) {
                $id_carriers = json_decode($id_carriers, true);
            }

            if (!is_array($id_carriers)) {
                $id_carriers = [$id_carriers];
            }

            $id_carriers = implode(',', $id_carriers);
        }

        if ($id_delivered) {
            if (!is_array($id_delivered)) {
                $id_delivered = json_decode($id_delivered, true);
            }

            if (!is_array($id_delivered)) {
                $id_delivered = [$id_delivered];
            }

            $id_delivered = implode(',', $id_delivered);
        }

        $db = Db::getInstance();

        // Tutti gli ordini senza tracking number
        $sql = new DbQuery();
        $sql->select('oc.id_order, oc.tracking_number')
            ->from('order_carrier', 'oc')
            ->innerJoin('orders', 'o', 'oc.id_order = o.id_order')
            ->where('o.id_carrier IN (' . $id_carriers . ')')
            ->where('o.date_add >= "' . $max_date . '"')
            ->groupBy('oc.id_order')
            ->having('MAX(oc.tracking_number) IS NULL OR MAX(oc.tracking_number) = ""')
            ->orderBy('o.id_order DESC');

        if ($id_delivered) {
            $sql->where('o.current_state NOT IN (' . $id_delivered . ')');
        }

        if ($id_state_skip && is_array($id_state_skip)) {
            $sql->where('o.current_state NOT IN (' . implode(',', $id_state_skip) . ')');
        }

        $noTracking = $db->executeS($sql);
        if (!$noTracking) {
            $noTracking = [];
        }

        // Tutti gli ordini con tracking number
        $sql = new DbQuery();
        $sql->select('oc.id_order, oc.tracking_number')
            ->from('order_carrier', 'oc')
            ->innerJoin('orders', 'o', 'oc.id_order = o.id_order')
            ->where('o.id_carrier IN (' . $id_carriers . ')')
            ->where('o.date_add >= "' . $max_date . '"')
            ->groupBy('oc.id_order')
            ->having('MAX(oc.tracking_number) IS NOT NULL AND MAX(oc.tracking_number) != ""')
            ->orderBy('o.id_order DESC');

        if ($id_delivered) {
            $sql->where('o.current_state NOT IN (' . $id_delivered . ')');
        }

        if ($id_state_skip && is_array($id_state_skip)) {
            $sql->where('o.current_state NOT IN (' . implode(',', $id_state_skip) . ')');
        }

        $tracking = $db->executeS($sql);
        if (!$tracking) {
            $tracking = [];
        }

        return [
            'status' => 'success',
            'totalShippings' => count($noTracking) + count($tracking),
            'list' => [
                'getTracking' => $noTracking,
                'getShipment' => $tracking,
            ],
        ];
    }

    /**
     * Recupera il tracking number delle spedizioni
     *
     * @param mixed $post_json parametri di input per la funzione
     *
     * @return array{processed: int, status: string, total: int}
     */
    public function ajaxFetchTracking($post_json)
    {
        $processed = 0;
        $orders = $post_json['list'];
        foreach ($orders as $order) {
            $id_order = (int) $order['id_order'];
            $tracking_number = TrackingNumber::get($id_order);

            if ($tracking_number) {
                $processed++;
                // Aggiorno order carrier inserendo il tracking
                $order['tracking_number'] = $tracking_number;
            }
        }

        return [
            'status' => 'success',
            'processed' => $processed,
            'total' => count($orders),
        ];
    }

    /**
     * Recupera le informazioni sulle spedizioni, processa gli stati e restituisce i dati
     *
     * @param array $post_json {"shipments_id": array} parametri di input per la funzione
     *
     * @return array{processed: int, response: array, status: string, total: int}
     */
    public function ajaxFetchShippingInfo($post_json)
    {
        $response = [];
        $processed = 0;
        $orders = $post_json['list'];
        foreach ($orders as $order) {
            if (!isset($order['id_order']) || !isset($order['tracking_number'])) {
                continue;
            }
            $id_order = (int) $order['id_order'];
            $tracking = $order['tracking_number'];
            $year = (new GetOrderShippingDate($id_order))->getShippingYear();

            $info = TrackingInfo::get($id_order, $tracking, $year);
            $shipmentData = TrackingInfo::prepareShipmentData($info);

            if ($shipmentData) {
                $processed++;
                $this->processEvents($info, $id_order, $tracking);
            }
        }

        return [
            'status' => 'success',
            'processed' => $processed,
            'total' => is_array($orders) ? count($orders) : 0,
            'response' => $response,
        ];
    }

    protected function processEvents($info, $id_order, $tracking_number)
    {
        $events = $info->getEventi();
        foreach ($events as $event) {
        }
    }

    /**
     * Invia la risposta al client per visualizzare i dati della spedizione
     *
     * @param array $shipmentData
     * @param int $id_order
     * @param string $tracking_number
     *
     * @return array
     */
    public function sendResponse($shipmentData, $id_order, $tracking_number)
    {
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
                'stato_attuale' => $this->getCurrentStatus($id_order),
                'storico' => $events,
                'days' => $shipmentData['days'] ?? '--',
            ],
        ];

        return $response;
    }

    /**
     * Recupera lo stato attuale della spedizione
     * 
     * @param int $id_order ID dell'ordine
     *
     * @return array Stato attuale della spedizione
     */
    public function getCurrentStatus($id_order)
    {
        // Recupera lo stato attuale dal database
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('e.*, h.*')
            ->from('mpbrtinfo_history', 'h')
            ->leftJoin('mpbrtinfo_evento', 'e', 'h.event_id = e.id_evento')
            ->where('h.id_order = ' . (int) $id_order)
            ->orderBy('h.date_add DESC');

        $result = $db->getRow($sql);

        if ($result) {
            return [
                'evento' => "({$result['event_id']} {$result['event_name']}",
                'data' => date('d/m/Y H:i', strtotime($result['event_date'])),
                'filiale' => "({$result['event_filiale_id']} {$result['event_filiale_name']}",
                'tipo' => '', // deprecated
            ];
        }

        // Se non ci sono dati nel database, restituisci uno stato predefinito
        return [
            'evento' => 'Nessuna informazione disponibile',
            'data' => date('d/m/Y H:i'),
            'filiale' => '-',
            'tipo' => 'sconosciuto',
        ];
    }

    /**
     * Conta i giorni lavorativi trascorsi dalla spedizione
     *
     * @param int $id_order
     *
     * @return int
     */
    public function countDays($id_order)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('days')
            ->from(ModelBrtHistory::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy(ModelBrtHistory::$definition['primary'] . ' DESC');

        return (int) $db->getValue($sql);
    }

    /**
     * Recupera i dettagli della spedizione BRT e li restituisce in formato JSON
     * Questa funzione è chiamata via AJAX dal template SweetAlert2
     */
    public function ajaxFetchOrderInfo($post_json)
    {
        // Verifica che i parametri necessari siano presenti
        if (!isset($post_json['id_order']) || !isset($post_json['tracking_number'])) {
            return [
                'success' => false,
                'message' => 'Parametri mancanti: id_order e tracking_number sono richiesti',
            ];
        }

        $id_order = (int) $post_json['id_order'];
        $tracking_number = pSQL($post_json['tracking_number']);
        $year_shipped = (new GetOrderShippingDate($id_order))->getShippingYear();
        if (!$year_shipped) {
            $year_shipped = date('Y');
        }

        // Verifica che l'ordine esista
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return [
                'success' => false,
                'message' => 'Ordine non trovato',
            ];
        }

        // Recupera il tracking number se non fornito
        if (!$tracking_number) {
            $tracking_number = TrackingNumber::get($id_order);
            if ($tracking_number && $tracking_number['esito'] == 0) {
                $tracking_number = $tracking_number['spedizione_id'];
                $db = Db::getInstance();
                $sql = new DbQuery();
                $sql->select('id_order_carrier')
                    ->from('order_carrier')
                    ->where('id_order = ' . (int) $id_order)
                    ->where('id_carrier = '. (int) $order->id_carrier)
                    ->orderBy('id_order_carrier DESC');
                $id_order_carrier = $db->getValue($sql);
                if ($id_order_carrier) {
                    try {
                        $result = $db->update(
                            'order_carrier',
                            [
                                'tracking_number' => $tracking_number
                            ],
                            'id_order_carrier = ' . (int) $id_order_carrier
                        );
                        if (!$result) {
                            return [
                                'success' => false,
                                'message' => 'Errore durante l\'aggiornamento del tracking number:' . $db->getMsgError(),
                            ];
                        }
                    } catch (\Throwable $th) {
                        return [
                            'success' => false,
                            'message' => 'Errore durante l\'aggiornamento del tracking number: ' . $th->getMessage(),
                        ];
                    }
                }
            }
        }

        try {
            // Recupera i dati della spedizione tramite SOAP
            $info = TrackingInfo::get($id_order, $tracking_number, $year_shipped);
            if ($info) {
                $shipmentData = TrackingInfo::prepareShipmentData($info);
            } else {
                return [
                    'success' => false,
                    'message' => 'Spedizione non trovata',
                ];
            }
            if (!isset($shipmentData['rmn'])) {
                return [
                    'success' => false,
                    'message' => implode('<br>', $shipmentData),
                ];
            }

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
                    'stato_attuale' => $this->getCurrentStatus($id_order),
                    'storico' => $events,
                    'days' => $shipmentData['days'] ?? '--',
                ],
            ];

            return $response;
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Errore durante il recupero dei dati della spedizione: ' . $e->getMessage(),
            ];
        }
    }
}
