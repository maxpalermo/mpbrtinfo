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

namespace MpSoft\MpBrtInfo\Fetch;

use MpSoft\MpBrtInfo\Helpers\ConvertIdColloToTracking;
use MpSoft\MpBrtInfo\Helpers\OrderEventExists;
use MpSoft\MpBrtInfo\Helpers\OrderEventsCount;
use MpSoft\MpBrtInfo\Helpers\OrderEventsCountDays;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoCountDays;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetCurrentOrderState;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTotalShippings;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoGetTrackingNumbers;
use MpSoft\MpBrtInfo\Helpers\OrdersInfoParseShippingData;
use MpSoft\MpBrtInfo\Helpers\Validate;
use MpSoft\MpBrtInfo\Mail\Mailer;
use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByIdCollo;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByRMA;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByRMN;
use MpSoft\MpBrtInfo\WSDL\GetLegendaEsiti;
use MpSoft\MpBrtInfo\WSDL\GetLegendaEventi;
use MpSoft\MpBrtInfo\WSDL\GetTrackingByBrtShipmentId;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FetchOrdersHandler
{
    protected $sessionJSON;
    protected $module;
    protected $context;

    public function __construct()
    {
        $data = file_get_contents('php://input');
        $this->sessionJSON = json_decode($data, true);
        $this->module = \Module::getInstanceByName('mpbrtinfo');
        $this->context = \Context::getContext();
    }

    protected function ajaxRender($message)
    {
        header('Content-Type: application/json');
        exit(json_encode($message));
    }

    public function run()
    {
        if (isset($this->sessionJSON['action']) && isset($this->sessionJSON['ajax'])) {
            $action = $this->sessionJSON['action'];
            if (method_exists($this, $action)) {
                $this->ajaxRender($this->$action($this->sessionJSON));
                exit;
            }
        }

        return $this->displayAjaxError();
    }

    public function displayAjaxError()
    {
        $this->ajaxRender(['error' => 'NO METHOD FOUND']);
    }

    public function getLegendaEventi($params)
    {
        $lang = $params['lang'];
        $last_update = $params['last_update'];

        $client = new GetLegendaEventi();
        $risultati = $client->getLegendaEventi($lang, $last_update);

        if ($risultati === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            return $risultati;
        }
    }

    protected function getLegendaEsiti($params)
    {
        $lang = $params['lang'];
        $last_update = $params['last_update'];

        $client = new GetLegendaEsiti();
        $risultati = $client->getLegendaEsiti($lang, $last_update);

        if ($risultati === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            return $risultati;
        }
    }

    protected function getIdSpedizioneByRMN($params)
    {
        $brt_customer_id = $params['brt_customer_id'];
        $rmn = $params['rmn'];
        // Crea un'istanza della classe
        $client = new GetIdSpedizioneByRMN();

        $risultato = $client->getIdSpedizione($brt_customer_id, $rmn);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }

    protected function getIdSpedizioneByRMA($params)
    {
        $brt_customer_id = $params['brt_customer_id'];
        $rma = $params['rma'];
        // Crea un'istanza della classe
        $client = new GetIdSpedizioneByRMA();

        $risultato = $client->getIdSpedizione($brt_customer_id, $rma);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }

    /**
     * Ottiene l'ID di una spedizione BRT tramite ID collo
     * 
     * @param array $params Parametri della richiesta
     *
     * @return array Risultato della chiamata SOAP
     */
    protected function getIdSpedizioneByIdCollo($params)
    {
        $brt_customer_id = $params['brt_customer_id'];
        $collo_id = $params['collo_id'];
        // Crea un'istanza della classe
        $client = new GetIdSpedizioneByIdCollo();

        $risultato = $client->getIdSpedizione($brt_customer_id, $collo_id);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }

    protected function showInfoPanel($params)
    {
        $id_order = (int) $params['id_order'];
        $tracking_number = $params['spedizione_id'];

        $info = $this->getTrackingByBrtShipmentId($params);
        if (isset($info['error'])) {
            return $info;
        }

        if (isset($info['ESITO']) && $info['ESITO'] >= 0) {
            $data = [
                'id_order' => $id_order,
                'tracking_number' => $tracking_number,
                'data' => $info,
            ];
            $parsedData = OrdersInfoParseShippingData::run($data);

            return $parsedData;
        }

        return $info;
    }

    protected function getHtmlPanel($data)
    {
    }

    /**
     * Ottiene le informazioni di tracking di una spedizione BRT tramite l'ID spedizione BRT
     * 
     * @param array $params Parametri della richiesta
     *
     * @return array Risultato della chiamata SOAP
     */
    protected function getTrackingByBrtShipmentId($params)
    {
        // Estrai i parametri dalla richiesta
        $spedizione_id = isset($params['spedizione_id']) ? $params['spedizione_id'] : '';
        $id_order = isset($params['id_order']) ? (int) $params['id_order'] : 0;
        $lingua_iso639_alpha2 = isset($params['lingua']) ? $params['lingua'] : 'IT';
        $anno = isset($params['spedizione_anno']) ? $params['spedizione_anno'] : '';
        // Crea un'istanza della classe
        $client = new GetTrackingByBrtShipmentId();

        // Esegui la chiamata al servizio
        $risultato = $client->getTracking($spedizione_id, $id_order, $lingua_iso639_alpha2, $anno);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(', ', $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }

    protected function parseShippingData($params)
    {
        return OrdersInfoParseShippingData::run($params);
    }

    protected function getAdminTemplatePath($name)
    {
        return $this->module->getLocalPath() . 'views/templates/admin/' . $name;
    }

    /**
     * Estrae i dati dalla risposta SOAP e restituisce un array
     * 
     * @param \MpSoft\MpBrtInfo\Bolla\Bolla $bolla Risposta SOAP
     * 
     * @return array|bool Dati estratti dalla risposta SOAP
     */
    protected function prepareShipmentData($bolla)
    {
        if ($bolla && $bolla->getEsito() == 0) {
            $spedizione = $bolla->getDatiSpedizione();
            $consegna = $bolla->getDatiConsegna();
            $merce = $bolla->getDatiMerce();
            $eventi = $bolla->getEventi();
            $riferimenti = $bolla->getRiferimenti();

            // Estrai i dati dalla risposta SOAP
            return [
                'rmn' => $riferimenti->getRiferimentoMittenteNumerico() ?? '--',
                'rma' => $riferimenti->getRiferimentoMittenteAlfabetico() ?? '--',
                'data_spedizione' => $spedizione->getSpedizioneData() ?? '--',
                'id_spedizione' => $spedizione->getSpedizioneId() ?? '--',
                'porto' => $spedizione->getTipoPorto() ?? '--',
                'servizio' => $spedizione->getTipoServizio() ?? '--',
                'colli' => $merce->getColli() ?? 0,
                'peso' => ($merce->getPesoKg() ?? '0') . ' Kg',
                'volume' => ($merce->getVolumeM3() ?? '0') . ' m3',
                'natura' => $merce->getNaturaMerce() ?? '--',
                'data_consegna' => $consegna->getDataConsegnaMerce() ?? '--',
                'eventi' => $eventi,
                'json' => $bolla->getBollaJson(),
            ];
        } else {
            return false;
        }
    }

    /**
     * Summary of prepareHistory
     *
     * @param mixed $id_order
     * @param mixed $year_shipped
     * @param \MpSoft\MpBrtInfo\Bolla\Bolla $bolla
     *
     * @return bool|string[]
     */
    protected function prepareHistory($id_order, $year_shipped, \MpSoft\MpBrtInfo\Bolla\Bolla $bolla)
    {
        $errors = [];

        $date_shipped = '';
        $date_delivered = '';
        $days = 0;

        $date_shipped = '';
        $date_delivered = '';
        foreach (array_reverse($bolla->getEventi()) as $evento) {
            $id_order_state = OrdersInfoGetCurrentOrderState::run($id_order);
            $filiale = $evento->getFiliale();
            $filiale_id = preg_match('/\((.*?)\)/', $filiale, $matches) ? trim($matches[1]) : '';
            $filiale_name = preg_match('/(.*)\(/', $filiale, $matches) ? trim($matches[1]) : '';
            $data_evento = $evento->getData() . ' ' . $evento->getOra();
            $date_event_iso = \DateTime::createFromFormat('d.m.Y H.i', $data_evento)->format('Y-m-d H:i:s');
            $evento_date_shipped = '';
            $evento_date_delivered = '';

            $orderEventCount = OrderEventsCount::get($id_order);

            if ($orderEventCount == 0) {
                // è il primo evento da inserire, aggiorno la data di spedizione
                $evento_date_shipped = $date_event_iso;
            }
            if ($evento->isDelivered()) {
                // l'ordine è stato consegnato, aggiorno la data di consegna
                $evento_date_delivered = $date_event_iso;
            }

            if ($evento_date_shipped && !$date_shipped) {
                if ($evento_date_shipped != '0000-00-00 00:00:00') {
                    $date_shipped = $evento_date_shipped;
                }
            }

            if ($evento_date_delivered && !$date_delivered) {
                if ($evento_date_delivered != '0000-00-00 00:00:00') {
                    $date_delivered = $evento_date_delivered;
                }
            }

            if ($date_shipped && $date_delivered) {
                $days = OrderEventsCountDays::get($id_order, $date_shipped, $date_delivered);
            }

            $fields = [
                'id_order' => $id_order,
                'id_order_state' => $id_order_state,
                'event_id' => $evento->getId(),
                'event_name' => $evento->getDescrizione(),
                'event_date' => $date_event_iso,
                'event_filiale_id' => $filiale_id,
                'event_filiale_name' => $filiale_name,
                'id_collo' => $bolla->getTrackingNumber(),
                'rmn' => $bolla->getRiferimenti()->getRiferimentoMittenteNumerico(),
                'rma' => $bolla->getRiferimenti()->getRiferimentoMittenteAlfabetico(),
                'anno_spedizione' => $year_shipped,
                'date_shipped' => $date_shipped,
                'date_delivered' => $date_delivered,
                'days' => $days,
                'json' => $bolla->getBollaJson(),
                'date_add' => date('Y-m-d H:i:s'),
            ];

            if ($fields['rmn'] && !Validate::isNumeric($fields['rmn'])) {
                $errors[] = 'RMN deve essere un numero';

                return false;
            }

            // Controllo che l'evento non sia già stato inserito
            $id = OrderEventExists::get($id_order, $fields['event_id'], $fields['event_date']);

            // Se l'evento non esiste, lo inserisco
            if (!$id) {
                $model = new \ModelBrtHistory();
                $model->hydrate($fields);

                try {
                    $model->add();

                    $id_order_state = (int) \ModelBrtEvento::getIdOrderStateByIdEvent((int) $fields['event_id']);
                    // Se bisogna cambiare stato, ci deve essere un id_stato valido
                    if ($id_order_state) {
                        $order = new \Order($id_order);
                        if (\Validate::isLoadedObject($order)) {
                            if ($order->current_state != $id_order_state) {
                                $order->setCurrentState($id_order_state);
                            }
                        }
                    }
                } catch (\Throwable $th) {
                    $errors[] = sprintf('Ordine %s: Errore %s', $model->id_order, $th->getMessage());
                }

                // Controllo se devo spedire un email associata all'evento
                $sendEmail = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_SEND_EMAIL, false);
                if ($sendEmail) {
                    $email = \ModelBrtEvento::getEmail($fields['event_id']);
                    if ($email) {
                        $trackingData = [
                            'tracking_number' => $fields['id_collo'],
                            'last_update' => $fields['event_date'],
                            'reason' => "({$fields['event_id']}) {$fields['event_name']}",
                            'id_event' => $fields['event_id'],
                        ];
                        $mailer = new Mailer();
                        $mailer->sendEmail($email, $id_order, $trackingData);
                    }
                }
            }
        }

        if (!$errors) {
            return true;
        }

        return $errors;
    }

    /**
     * Ottiene lo stato corrente dell'ordine
     * 
     * @param int $id_order ID dell'ordine
     * 
     * @return int ID dello stato corrente
     */
    protected function getCurrentOrderState($id_order)
    {
        return OrdersInfoGetCurrentOrderState::run($id_order);
    }

    /**
     * Conta i giorni lavorativi trascorsi dalla spedizione
     *
     * @param int $id_order
     *
     * @return int
     */
    protected function countDays($id_order)
    {
        return OrdersInfoCountDays::run($id_order);
    }

    public function getTotalShippings($params)
    {
        return OrdersInfoGetTotalShippings::run();
    }

    public function getTrackingNumbers($params)
    {
        $list = $params['list'] ?? [];

        return OrdersInfoGetTrackingNumbers::run($list);
    }

    /**
     * Ottiene le informazioni di tracking di una spedizione BRT tramite l'ID spedizione BRT
     * 
     * @param array $params Parametri della richiesta
     *
     * @return array Risultato della chiamata SOAP
     */
    protected function getTrackingsByBrtShipmentId($params)
    {
        $processed = 0;
        $list = $params['list'] ?? [];
        $lingua_iso639_alpha2 = isset($params['lingua']) ? $params['lingua'] : '';

        if ($list && is_array($list)) {
            foreach ($list as &$item) {
                $id_order = $item['id_order'];
                $shipping_year = (new GetOrderShippingDate($id_order))->getShippingYear();
                // Mi assicuro che il tracking non sia un ID COLLO
                $tracking_number = ConvertIdColloToTracking::convert($item['tracking_number']);

                $order = new \Order($id_order);
                if (!\Validate::isLoadedObject($order)) {
                    $item['status'] = 'error';
                    $item['message'] = 'Ordine non trovato';

                    continue;
                }

                if ($tracking_number) {
                    // Crea un'istanza della classe
                    $client = new GetTrackingByBrtShipmentId();

                    // Esegui la chiamata al servizio
                    $response = $client->getTracking($tracking_number, $id_order, $lingua_iso639_alpha2, $shipping_year);

                    // Controlla che l'esito sia andato a buon fine
                    if ($response['ESITO'] < 0) {
                        $item['status'] = 'error';
                        $item['message'] = 'Informazione spedizione non presente';

                        continue;
                    }

                    // Controlla l'esito, aggiorna lo stato, invia l'email
                    $parsedData = $this->parseShippingData(
                        [
                            'id_order' => $id_order,
                            'tracking_number' => $tracking_number,
                            'data' => $response,
                        ]
                    );

                    if ($parsedData['success'] == 'true') {
                        $item['status'] = 'success';
                        $processed++;
                    } else {
                        $item['status'] = 'error';
                        $item['message'] = 'Informazione spedizione non presente';
                    }
                }
            }
        }

        return [
            'status' => 'success',
            'processed' => $processed,
            'total' => count($list),
            'list' => $list,
        ];
    }

    public function updateIcon($params)
    {
        $id_order = $params['id_order'];
        $lastEvent = \ModelBrtEvento::getLastEventHistory($id_order);
        $event = \ModelBrtEvento::getEvento($lastEvent['event_id']);

        if ($event) {
            return [
                'color' => $event->color,
                'icon' => $event->icon,
            ];
        }

        return [
            'color' => 'red',
            'icon' => 'error',
        ];
    }
}
