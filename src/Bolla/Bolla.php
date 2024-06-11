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

namespace MpSoft\MpBrtInfo\Bolla;
use MpSoft\MpBrtInfo\Ajax\AjaxInsertEventiSQL;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Bolla
{
    /** @var Assicurazione */
    protected $assicurazione;
    /** @var Contrassegno */
    protected $contrassegno;
    /** @var DatiConsegna */
    protected $dati_consegna;
    /** @var DatiSpedizione */
    protected $dati_spedizione;
    /** @var Destinatario */
    protected $destinatario;
    /** @var Merce */
    protected $merce;
    /** @var Mittente */
    protected $mittente;
    /** @var Riferimenti */
    protected $riferimenti;
    /** @var int */
    protected $contatore_eventi;
    /** @var int */
    protected $contatore_note;
    /** @var int */
    protected $esito;
    /** @var string */
    protected $esito_desc;
    /** @var array */
    protected $eventi;
    /** @var array */
    protected $note;
    /** @var string */
    protected $timestamp;
    /** @var string */
    protected $versione;
    protected static $event_list = [];
    protected $customer;

    public function __construct($bolla)
    {
        $this->assicurazione = isset($bolla['BOLLA']['ASSICURAZIONE']) ? new Assicurazione($bolla['BOLLA']['ASSICURAZIONE']) : null;
        $this->contrassegno = isset($bolla['BOLLA']['CONTRASSEGNO']) ? new Contrassegno($bolla['BOLLA']['CONTRASSEGNO']) : null;
        $this->dati_consegna = isset($bolla['BOLLA']['DATI_CONSEGNA']) ? new DatiConsegna($bolla['BOLLA']['DATI_CONSEGNA']) : null;
        $this->dati_spedizione = isset($bolla['BOLLA']['DATI_SPEDIZIONE']) ? new DatiSpedizione($bolla['BOLLA']['DATI_SPEDIZIONE']) : NULL;
        $this->destinatario = ISSET($Bolla['BOLLA']['DESTINATARIO']) ? new Destinatario($bolla['BOLLA']['DESTINATARIO']) : null;
        $this->merce = isset($bolla['BOLLA']['MERCE']) ? new Merce($bolla['BOLLA']['MERCE']) : null;
        $this->mittente = isset($bolla['BOLLA']['MITTENTE']) ? new Mittente($bolla['BOLLA']['MITTENTE']) : null;
        $this->riferimenti = isset($bolla['BOLLA']['RIFERIMENTI']) ? new Riferimenti($bolla['BOLLA']['RIFERIMENTI']) : null;
        $this->contatore_eventi = isset($bolla['CONTATORE EVENTI']) ? $bolla['CONTATORE EVENTI'] : 0;
        $this->contatore_note = isset($bolla['CONTATORE_NOTE']) ? $bolla['CONTATORE_NOTE'] : 0;
        $this->esito = isset($bolla['ESITO']) ? $bolla['ESITO'] : -99;
        $this->esito_desc = isset($bolla['ESITO_DESC']) ? $bolla['ESITO_DESC'] : '';
        $this->timestamp = isset($bolla['TIMESTAMP']) ? $bolla['TIMESTAMP'] : '';
        $this->versione = isset($bolla['VERSIONE']) ? $bolla['VERSIONE'] : '';
        if (isset($bolla['LISTA_EVENTI'])) {
            $this->addEventi($bolla['LISTA_EVENTI']);
        }
        if (isset($bolla['NOTE'])) {
            $this->addNote($bolla['NOTE']);
        }
        self::$event_list = (new AjaxInsertEventiSQL())->getList();
    }

    // Getter methods
    public function getCustomer()
    {
        $order = new \Order((int) $this->getRiferimenti()->getRiferimentoMittenteNumerico());
        if (\Validate::isLoadedObject($order)) {
            $customer = new \Customer($order->id_customer);
            if (\Validate::isLoadedObject($customer)) {
                return $customer->firstname . ' ' . $customer->lastname;
            }
        }

        return '--';
    }

    public function getAssicurazione()
    {
        return $this->assicurazione;
    }

    public function getContrassegno()
    {
        return $this->contrassegno;
    }

    public function getDatiConsegna()
    {
        return $this->dati_consegna;
    }

    public function getDatiSpedizione()
    {
        return $this->dati_spedizione;
    }

    public function getDestinatario()
    {
        return $this->destinatario;
    }

    public function getMerce()
    {
        return $this->merce;
    }

    public function getMittente()
    {
        return $this->mittente;
    }

    public function getRiferimenti()
    {
        return $this->riferimenti;
    }

    public function getContatoreEventi()
    {
        return $this->contatore_eventi;
    }

    public function getContatoreNote()
    {
        return $this->contatore_note;
    }

    public function getEsito()
    {
        return $this->esito;
    }

    public function getEsitoDesc()
    {
        return $this->esito_desc;
    }

    public function getEventi()
    {
        return $this->eventi;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getVersione()
    {
        return $this->versione;
    }

    // Setter methods
    public function setAssicurazione($assicurazione)
    {
        $this->assicurazione = $assicurazione;
    }

    public function setContrassegno($contrassegno)
    {
        $this->contrassegno = $contrassegno;
    }

    public function setDatiConsegna($dati_consegna)
    {
        $this->dati_consegna = $dati_consegna;
    }

    public function setDatiSpedizione($dati_spedizione)
    {
        $this->dati_spedizione = $dati_spedizione;
    }

    public function setDestinatario($destinatario)
    {
        $this->destinatario = $destinatario;
    }

    public function setMerce($merce)
    {
        $this->merce = $merce;
    }

    public function setMittente($mittente)
    {
        $this->mittente = $mittente;
    }

    public function setRiferimenti($riferimenti)
    {
        $this->riferimenti = $riferimenti;
    }

    public function setContatoreEventi($contatore_eventi)
    {
        $this->contatore_eventi = $contatore_eventi;
    }

    public function setContatoreNote($contatore_note)
    {
        $this->contatore_note = $contatore_note;
    }

    public function setEsito($esito)
    {
        $this->esito = $esito;
    }

    public function setEsitoDesc($esito_desc)
    {
        $this->esito_desc = $esito_desc;
    }

    public function setEventi($eventi)
    {
        $this->eventi = $eventi;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function setVersione($versione)
    {
        $this->versione = $versione;
    }

    protected function addEventi($eventi)
    {
        foreach ($eventi as $evento) {
            $this->eventi[] = new Evento($evento['EVENTO']);
        }
    }

    protected function addNote($note)
    {
        foreach ($note as $nota) {
            $this->note[] = new Nota($nota['NOTA']);
        }
    }

    public function getLastEvento()
    {
        if ($this->eventi) {
            return reset($this->eventi);
        }

        return new Evento([]);
    }

    public function getLastEvent()
    {
        return $this->getLastEvento();
    }

    public function getColorEvento(Evento $evento = null)
    {
        if (!$evento) {
            $evento = $this->getLastEvento();
        }
        if (!$evento) {
            return 'secondary';
        }

        return $evento->getColor();
    }

    public function updateState($id_order)
    {
        $evento = $this->getLastEvent();
        $rmn = $this->getRiferimenti()->getRiferimentoMittenteNumerico();
        $id_collo = $this->getDatiSpedizione()->getSpedizioneId();
        self::changeIdOrderState($id_order, $evento, $rmn, $id_collo);
    }

    public static function changeIdOrderState(int $id_order, Evento $evento, $rmn, $id_collo)
    {
        $id_state_sent = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);
        $id_state_transit = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT);
        $id_state_waiting = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING);
        $id_state_delivered = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
        $id_state_error = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR);
        $id_state_fermopoint = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT);
        $id_state_refused = \ModelBrtConfig::getConfigValue(\ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED);
        $id_order_state = 0;
        $order_state = '';
        $event_list = self::$event_list;

        if ($evento->isSent()) {
            $id_order_state = $id_state_sent;
        } elseif ($evento->isTransit()) {
            $id_order_state = $id_state_transit;
        } elseif ($evento->isWaiting() && $evento->isFermopoint()) {
            $id_order_state = $id_state_fermopoint;
        } elseif ($evento->isDelivered() && $evento->isFermopoint()) {
            $id_order_state = $id_state_delivered;
        } elseif ($evento->isWaiting()) {
            $id_order_state = $id_state_waiting;
        } elseif ($evento->isDelivered()) {
            $id_order_state = $id_state_delivered;
        } elseif ($evento->isError()) {
            $id_order_state = $id_state_error;
        } elseif ($evento->isFermopoint()) {
            $id_order_state = $id_state_fermopoint;
        } elseif ($evento->isRefused()) {
            $id_order_state = $id_state_refused;
        } else {
            $id_order_state = 0;
        }

        if ($id_order_state == 0) {
            return false;
        }

        $order_state = $event_list[$evento->getId()] ?? '';
        $last_brt_state = \ModelBrtTrackingNumber::getLastState($id_order);

        if ($last_brt_state && $last_brt_state == $order_state['id_evento']) {
            return false;
        }

        $model = new \ModelBrtTrackingNumber();
        $model->id_order = $id_order;
        $model->id_order_state = $id_order_state;
        $model->id_brt_state = $order_state['id_evento'];
        $model->date_event = date('Y-m-d H:i:s', strtotime($evento->getData() . ' ' . $evento->getOra()));
        $model->tracking_number = '';
        $model->rmn = $rmn;
        $model->id_collo = $id_collo;
        $model->current_state = $order_state['name'];
        $model->anno_spedizione = date('Y', strtotime($evento->getData() . ' ' . $evento->getOra()));
        $model->date_add = date('Y-m-d H:i:s');
        $model->add();

        $order = new \Order($id_order);
        $current_state = $order->getCurrentState();
        if ($current_state == $id_order_state) {
            return false;
        }

        $order->setCurrentState($id_order_state);

        return sprintf('Ordine %s: stato cambiato da %s a %s', $id_order, $current_state, $id_order_state);
    }
}
