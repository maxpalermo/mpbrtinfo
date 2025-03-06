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

class TrackingInfo
{
    /**
     * Summary of get
     *
     * @param mixed $tracking_number
     * @param mixed $year
     *
     * @return \MpSoft\MpBrtInfo\Bolla\Bolla|bool
     */
    public static function get($id_order, $tracking_number, $year)
    {
        $info = (new GetIdSpedizioneInfo($tracking_number, $year))->getInfo();
        if ($info) {
            $historyResult = self::prepareHistory($id_order, $year, $info);
            /* if ($historyResult !== true) {
                return $historyResult;
            } */
        }

        return $info;
    }

    /**
     * Ottiene lo stato corrente dell'ordine
     * 
     * @param int $id_order ID dell'ordine
     * 
     * @return int ID dello stato corrente
     */
    public static function getCurrentOrderState($id_order)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('current_state')
            ->from('orders')
            ->where('id_order = ' . (int) $id_order);

        return (int) $db->getValue($sql);
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
    public static function prepareHistory($id_order, $year_shipped, \MpSoft\MpBrtInfo\Bolla\Bolla $bolla)
    {
        $errors = [];

        $date_shipped = '';
        $date_delivered = '';
        $days = 0;

        foreach (array_reverse($bolla->getEventi()) as $evento) {
            $id_order_state = self::getCurrentOrderState($id_order);
            $filiale = $evento->getFiliale();
            $filiale_id = preg_match('/\((.*?)\)/', $filiale, $matches) ? trim($matches[1]) : '';
            $filiale_name = preg_match('/(.*)\(/', $filiale, $matches) ? trim($matches[1]) : '';
            $data_evento = $evento->getData() . ' ' . $evento->getOra();
            $date = \DateTime::createFromFormat('d.m.Y H.i', $data_evento);

            if ($evento->getId() == 701 && !$date_shipped) {
                $date_shipped = $date->format('Y-m-d H:i:s');
            }

            if ($evento->isDelivered() && !$date_delivered) {
                $date_delivered = $date->format('Y-m-d H:i:s');
            }

            if ($date_shipped && $date_delivered) {
                $days = \ModelBrtHistory::countDays($date_shipped, $date_delivered);
            }

            $fields = [
                'id_order' => $id_order,
                'id_order_state' => $id_order_state,
                'event_id' => $evento->getId(),
                'event_name' => $evento->getDescrizione(),
                'event_date' => $date->format('Y-m-d H:i:s'),
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
                'date_add' => date( 'Y-m-d H:i:s' ),
            ];

            // Controllo che l'evento non sia già stato inserito
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select(\ModelBrtHistory::$definition['primary'])
                ->from(\ModelBrtHistory::$definition['table'])
                ->where('id_order = ' . (int) $id_order)
                ->where('event_date = \'' . $fields['event_date'] . '\'')
                ->where('event_id = \'' . pSQL($fields['event_id']) . '\'');

            $id = $db->getValue($sql);

            // Se l'evento non esiste, lo inserisco
            if (!$id) {
                $model = new \ModelBrtHistory();
                $model->hydrate($fields);

                try {
                    $model->add();

                    $id_order_state = $evento->getOrderStateIdByEventId();
                    $order = new \Order($id_order);
                    if (\Validate::isLoadedObject($order)) {
                        if ($order->current_state != $id_order_state) {
                            $order->setCurrentState($id_order_state);
                        }
                    }
                } catch (\Throwable $th) {
                    $errors[] = sprintf('Ordine %s: Errore %s', $model->id_order, $th->getMessage());
                }
            }
        }

        if (!$errors) {
            return true;
        }

        return $errors;
    }

    public static function prepareShipmentData($bolla)
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
}