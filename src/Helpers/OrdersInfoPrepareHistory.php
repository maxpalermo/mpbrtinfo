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

namespace MpSoft\MpBrtInfo\Helpers;

use MpSoft\MpBrtInfo\Mail\Mailer;

class OrdersInfoPrepareHistory
{
    /**
     * Inserisce i dati di spedizione e consegna nella tabella `history`
     *
     * @param int $id_order ID dell'ordine
     * @param int $year_shipped Anno di spedizione
     * @param \MpSoft\MpBrtInfo\Bolla\Bolla $bolla Bolla
     *
     * @return array|true true se tutto ok, array di errori altrimenti
     */
    public static function run($id_order, $year_shipped, \MpSoft\MpBrtInfo\Bolla\Bolla $bolla)
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
                \PrestaShopLogger::addLog('RMN deve essere un numero', 3, 0, 'OrderFetch', $id_order);

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
                    \PrestaShopLogger::addLog($th->getMessage(), 3, $th->getCode(), 'PrepareHistoryData', $id_order);
                    \PrestaShopLogger::addLog(json_encode($model), 1, 0, 'PrepareHistoryData', $id_order);
                    $error = sprintf('Ordine %s: Errore %s', $id_order, $th->getMessage());
                    \PrestaShopLogger::addLog($error, 3, $th->getCode(), 'PrepareHistoryData', $id_order);

                    $errors[] = $error;
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
}
