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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Evento
{
    protected $data;
    protected $descrizione;
    protected $filiale;
    protected $id;
    protected $ora;
    protected $is_shipped;
    protected $is_delivered;
    protected $row;
    protected $id_order;

    public function __construct($evento, $id_order = 0)
    {
        $this->id_order = $id_order;

        if (!$evento) {
            $this->data = '';
            $this->descrizione = '';
            $this->filiale = '';
            $this->id = '';
            $this->ora = '';
        } else {
            $this->data = $evento['DATA'];
            $this->descrizione = $evento['DESCRIZIONE'];
            $this->filiale = $evento['FILIALE'];
            $this->id = $evento['ID'];
            $this->ora = $evento['ORA'];
        }

        $this->row = $this->getEventFull();
    }

    public function getRow()
    {
        return $this->row;
    }

    /**
     * Restituisce un oggetto EVENTO da una riga evento della tabella
     *
     * @param array $event_row
     *
     * @return bool|Evento
     */
    public static function getEventoByEventRow($event_row)
    {
        if (!$event_row) {
            return false;
        }

        try {
            $date = date('d.m.Y', strtotime($event_row['event_date']));
            $ora = date('H:i', strtotime($event_row['event_date']));
            $filiale = "{$event_row['event_filiale_name']} ({$event_row['event_filiale_id']})";
            $event = [
                'DATA' => $date,
                'DESCRIZIONE' => $event_row['event_name'],
                'FILIALE' => $filiale,
                'ID' => $event_row['event_id'],
                'ORA' => $ora,
            ];

            return new Evento($event);
        } catch (\Throwable $th) {
            \PrestaShopLogger::addLog($th->getMessage(), 3, $th->getCode(), 'Evento', $th->getLine(), true);

            return false;
        }
    }

    public static function getEventRowsByEventType($event_type)
    {
        if (!$event_type) {
            return false;
        }

        if ($event_type == 'shipped') {
            $event_type = 'sent';
        }

        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('*')
            ->from('mpbrtinfo_evento')
            ->where("is_{$event_type} = 1")
            ->orderBy('id_evento ASC, name ASC');

        return $db->executeS($sql);
    }

    /**
     * Summary of getOrderEventById
     *
     * @param mixed $id_order
     * @param mixed $event_id
     *
     * @return bool|Evento
     */
    public static function getOrderEventById($id_order, $event_id)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('*')
            ->from(\ModelBrtHistory::$definition['table'])
            ->where('id_order=' . (int) $id_order)
            ->where('event_id=' . (int) $event_id)
            ->orderBy(\ModelBrtHistory::$definition['primary'] . ' DESC');

        $row = $db->getRow($sql);

        if ($row) {
            $data = date('d.m.Y', strtotime($row['event_date']));
            $ora = date('H:i', strtotime($row['event_date']));
            $filiale = "{$row['event_filiale_name']} ({$row['event_filiale_id']})";

            $fields = [
                'DATA' => $data,
                'ORA' => $ora,
                'DESCRIZIONE' => $row['event_name'],
                'FILIALE' => $filiale,
                'ID' => $row['event_id'],
            ];

            return new Evento($fields);
        }

        return false;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getDescrizione()
    {
        return $this->descrizione;
    }

    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;
    }

    public function getFiliale()
    {
        return $this->filiale;
    }

    public function setFiliale($filiale)
    {
        $this->filiale = $filiale;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getOra()
    {
        return $this->ora;
    }

    public function setOra($ora)
    {
        $this->ora = $ora;
    }

    protected function getEventFull()
    {
        $eventFull = \ModelBrtEvento::getEventFull($this->id_order, $this->id);

        return $eventFull;
    }

    public function isDelivered()
    {
        $event = \ModelBrtEvento::getEvento($this->id);
        if (\Validate::isLoadedObject($event)) {
            $this->is_delivered = (int) $event->is_delivered;

            return $this->is_delivered;
        }

        return false;
    }

    public function isShipped()
    {
        $event = \ModelBrtEvento::getEvento($this->id);
        if (\Validate::isLoadedObject($event)) {
            $this->is_shipped = (int) $event->is_shipped;

            return $this->is_shipped;
        }

        return false;
    }

    public function getRowData()
    {
        return $this->row;
    }

    public function getListEventi()
    {
        $ps_mpbrtinfo_evento = [
            ['id_evento' => 'ZAC', 'name' => 'ACQUA ALTA', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => 'ZAL', 'name' => 'ALLUVIONE/NUBIFRAGIO', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => '722', 'name' => 'ARRIVATA AL BRT-fermopoint', 'icon' => 'local_shipping', 'color' => '#FB8C00'],
            ['id_evento' => '703', 'name' => 'ARRIVATA IN FILIALE', 'icon' => 'store', 'color' => '#4CAF50'],
            ['id_evento' => '017', 'name' => 'ASSENTE DOPO LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'ZBC', 'name' => 'BLOCCO CIRCOLAZIONE', 'icon' => 'block', 'color' => '#EF5350'],
            ['id_evento' => 'ZBS', 'name' => 'BLOCCO STRADALE', 'icon' => 'block', 'color' => '#EF5350'],
            ['id_evento' => 'ZFM', 'name' => 'CAUSA FORZA MAGGIORE', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '019', 'name' => 'CESSATA ATTIVITA\' DESTINATARIO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '026', 'name' => 'CHIESTA CONSEGNA ALTRO INDIR.', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'T', 'name' => 'CHIUSO PER TURNO', 'icon' => 'schedule', 'color' => '#9E9E9E'],
            ['id_evento' => 'MAN', 'name' => 'COLLO/I MANCANTE/I', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '704', 'name' => 'CONSEGNATA', 'icon' => 'check_circle', 'color' => '#4CAF50'],
            ['id_evento' => 'P', 'name' => 'CONSEGNATA PARZIALMENTE', 'icon' => 'check_circle', 'color' => '#4CAF50'],
            ['id_evento' => 'IDD', 'name' => 'CONTATTARE FILIALE', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'N', 'name' => 'DA CONSEGNARE', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => 'DPU', 'name' => 'DA RITIRARE AL PARCEL SHOP', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => '045', 'name' => 'DATI MANCANTI PER LA FATTURA', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '700', 'name' => 'DATI SPEDIZ. TRASMESSI A BRT', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => 'AVV', 'name' => 'Destin.Assente:LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'AV2', 'name' => 'Destin.Assente:LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'AV3', 'name' => 'Destin.Assente:LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'AV5', 'name' => 'Destin.Assente:LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'AV7', 'name' => 'Destin.Assente:LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'AV9', 'name' => 'Destin.Assente:LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'RIC', 'name' => 'Destin.Assente:LASCIATO AVVISO', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => '021', 'name' => 'DESTINATAR.SCONOSC./INCOMPLETO', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => 'A23', 'name' => 'DESTINATARIO CHIUSO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '023', 'name' => 'DESTINATARIO CHIUSO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '024', 'name' => 'DESTINATARIO CHIUSO PER FERIE', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => 'ZDM', 'name' => 'DISAGI DOPO MANIFESTAZIONE', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => 'DDC', 'name' => 'DISTRUTTA/REQUISITA DA DOGANA', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => 'DDB', 'name' => 'DOCUMENTI DOGANALI MANCANTI', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '035', 'name' => 'DOCUMENTI INCOMPLETI/MANCANTI', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '028', 'name' => 'ESERCIZIO NON IN ATTIVITA\'', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => 'DDS', 'name' => 'FERMA PER CONTROLLI DOGANALI', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '032', 'name' => 'FERMO DEPOSITO:NESSUNO RITIRA', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => 'ZFR', 'name' => 'FESTIVITA REGIONALE', 'icon' => 'event', 'color' => '#9E9E9E'],
            ['id_evento' => 'PAT', 'name' => 'FESTIVITA\' PATRONALE', 'icon' => 'event', 'color' => '#9E9E9E'],
            ['id_evento' => 'ZSF', 'name' => 'GIORNATA SEMI-FESTIVA', 'icon' => 'event', 'color' => '#9E9E9E'],
            ['id_evento' => 'G02', 'name' => 'IN ATTESA APERTURA GIACENZA 2G', 'icon' => 'schedule', 'color' => '#9E9E9E'],
            ['id_evento' => 'G03', 'name' => 'IN ATTESA APERTURA GIACENZA 3G', 'icon' => 'schedule', 'color' => '#9E9E9E'],
            ['id_evento' => 'G05', 'name' => 'IN ATTESA APERTURA GIACENZA 5G', 'icon' => 'schedule', 'color' => '#9E9E9E'],
            ['id_evento' => 'G09', 'name' => 'IN ATTESA APERTURA GIACENZA 9G', 'icon' => 'schedule', 'color' => '#9E9E9E'],
            ['id_evento' => 'MIC', 'name' => 'IN CONSEGNA', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => 'G', 'name' => 'IN GIACENZA', 'icon' => 'inventory', 'color' => '#9E9E9E'],
            ['id_evento' => 'GEN', 'name' => 'IN GIACENZA', 'icon' => 'inventory', 'color' => '#9E9E9E'],
            ['id_evento' => '022', 'name' => 'INDIRIZ.INESISTENTE/INCOMPLETO', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => 'DIR', 'name' => 'INOLTRO ALTRA FILIALE', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => '707', 'name' => 'INOLTRO ALTRA FILIALE', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => 'ZEE', 'name' => 'INTERRUZIONE ENERGIA ELETTRICA', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '055', 'name' => 'LOCKER GUASTO', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '056', 'name' => 'LOCKER PIENO', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => 'ZMP', 'name' => 'MANIFESTAZIONE PUBBLICA', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => 'ZMS', 'name' => 'MANIFESTAZIONE SPORTIVA', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => 'ZMM', 'name' => 'MARE MOLTO MOSSO', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => 'ZNV', 'name' => 'NEVICATA ECCEZIONALE', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => '034', 'name' => 'NON CONSEGNAB.FORZA MAGGIORE', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => 'DPP', 'name' => 'NON RITIRATA AL PARCEL SHOP', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '702', 'name' => 'PARTITA', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => '044', 'name' => 'PINCODE ERRATO O MANCANTE', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => '710', 'name' => 'PREAVVISO DI DANNO', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => '709', 'name' => 'PROPOSTA LIQUID.NE TRANSATTIVA', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => 'ZRO', 'name' => 'RALLENTAMENTI OPERATIVI', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => 'SIP', 'name' => 'REINDIRIZZATA A BRT-fermopoint', 'icon' => 'local_shipping', 'color' => '#FB8C00'],
            ['id_evento' => '708', 'name' => 'RESO AL MITTENTE', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '037', 'name' => 'RIFIUTA CONSEGNA TASSATIVA', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '001', 'name' => 'RIFIUTA SENZA MOTIVAZIONE', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '009', 'name' => 'RIFIUTA:CHIEDE CONTROLLO MERCE', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '004', 'name' => 'RIFIUTA:MERCE GIA\' RICEVUTA', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '002', 'name' => 'RIFIUTA:MERCE NON ORDINATA', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => 'DDR', 'name' => 'RIFIUTA:NON PAGA DAZI DOGANALI', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '012', 'name' => 'RIFIUTA:NON PAGA TRASPORTO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '008', 'name' => 'RIFIUTA:NON RICEVE C/ASSEGNO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '006', 'name' => 'RIFIUTA:RESO NON AUTORIZZATO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '005', 'name' => 'RIFIUTA:SPEDITA IN ANTICIPO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '003', 'name' => 'RIFIUTA:SPEDITA IN RITARDO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '050', 'name' => 'RIFIUTATA DAL BRT-fermopoint', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '051', 'name' => 'RIFIUTATA DAL DESTINATARIO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => '007', 'name' => 'RIFIUTO PER COLLO DANNEGGIATO', 'icon' => 'cancel', 'color' => '#D32F2F'],
            ['id_evento' => 'A16', 'name' => 'RIMANDA LA CONSEGNA', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => '016', 'name' => 'RIMANDA LA CONSEGNA', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => '100', 'name' => 'RIMANDA LA CONSEGNA', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => '101', 'name' => 'RIMANDA LA CONSEGNA', 'icon' => 'info', 'color' => '#FFEB3B'],
            ['id_evento' => '701', 'name' => 'RITIRATA', 'icon' => 'check_circle', 'color' => '#4CAF50'],
            ['id_evento' => '724', 'name' => 'RITIRATA AL BRT-fermopoint', 'icon' => 'local_shipping', 'color' => '#FB8C00'],
            ['id_evento' => '052', 'name' => 'SCADUTI TERMINI PER IL RITIRO', 'icon' => 'error', 'color' => '#EF5350'],
            ['id_evento' => 'ZSC', 'name' => 'SCIOPERO', 'icon' => 'warning', 'color' => '#FFA726'],
            ['id_evento' => '027', 'name' => 'SPEDIZIONE IN TRANSITO', 'icon' => 'local_shipping', 'color' => '#2196F3'],
            ['id_evento' => 'ZTR', 'name' => 'TERREMOTO', 'icon' => 'warning', 'color' => '#FFA726'],
        ];

        return $ps_mpbrtinfo_evento;
    }

    public static function getLastEventByOrderId($id_order)
    {
        $context = \Context::getContext();
        $db = \Db::getInstance();
        // Cerco l'id carrier dell'ordine
        $sql = new \DbQuery();
        $sql->select('c.id_carrier, c.name')
            ->from ('carrier', 'c')
            ->innerJoin('orders', 'o', 'o.id_carrier = c.id_carrier')
            ->where('o.id_order=' . (int) $id_order);
        $carrier = $db->getRow($sql);
        if (!$carrier) {
            return false;
        }

        // Cerco nello storico l'ultimo evento dell'ordine
        $sql = new \DbQuery();
        $sql->select('*')
            ->from(\ModelBrtHistory::$definition['table'])
            ->where('id_order=' . (int) $id_order)
            ->orderBy(\ModelBrtHistory::$definition['primary'] . ' DESC');
        $last_event = $db->getRow($sql);

        // Se esiste un evento prelevo le informazioni
        if ($last_event) {
            unset($last_event['json']);
            $sql = new \DbQuery();
            $sql->select('*')
                ->from(\ModelBrtEvento::$definition['table'])
                ->where('id_evento = ' . (int) $last_event['event_id']);
            $evento = $db->getRow($sql);
            if ($evento) {
                $evento = array_merge($last_event, $evento);
            }
            $evento['carrier_name'] = $carrier['name'];
            $evento['id_carrier'] = $carrier['id_carrier'];
            $evento['link'] = '';
            $evento['image'] = '';
        } else {
            $evento = [
                'id_evento' => 0,
                'id_order' => $id_order,
                'id_carrier' => $carrier['id_carrier'],
                'id_collo' => 0,
                'carrier_name' => $carrier['name'],
                'event_id' => 0,
                'event_name' => '',
                'event_date' => '',
                'rmn' => '',
                'rma' => '',
                'title' => $carrier['name'],
                'link' => '',
                'image' => $context->link->getMediaLink('/img/s/' . $carrier['id_carrier'] . '.jpg'),
                'icon' => '',
                'color' => '#ACACAC',
                'date_shipped' => '',
                'date_delivered' => '',
                'days' => 0,
                'anno_spedizione' => '',
            ];
        }

        return $evento;

        // Se esiste uno stato associato cerco l'evento corrispondente
        if ($id_mpbrtinfo_evento) {
            $sql = new DbQuery();
            $sql->select('*')
                ->from(ModelBrtEvento::$definition['table'])
                ->where('id_order = ' . (int) $id_order)
                ->orderBy('id_mpbrtinfo_evento DESC');
        }

        $icon = $this->displayCarrier->display($id_order);

        return $icon;
    }

    public static function getEventByIdEvent($id_event, $id_order)
    {
        $context = \Context::getContext();
        $db = \Db::getInstance();
        // Cerco l'id carrier dell'ordine
        $sql = new \DbQuery();
        $sql->select('c.id_carrier, c.name')
            ->from ('carrier', 'c')
            ->innerJoin('orders', 'o', 'o.id_carrier = c.id_carrier')
            ->where('o.id_order=' . (int) $id_order);
        $carrier = $db->getRow($sql);
        if (!$carrier) {
            return false;
        }

        // Cerco nello storico l'ultimo evento dell'ordine
        $sql = new \DbQuery();
        $sql->select('*')
            ->from(\ModelBrtHistory::$definition['table'])
            ->where('id_order=' . (int) $id_order)
            ->where('event_id=' . (int) $id_event)
            ->orderBy(\ModelBrtHistory::$definition['primary'] . ' DESC');
        $last_event = $db->getRow($sql);

        // Se esiste un evento prelevo le informazioni
        if ($last_event) {
            unset($last_event['json']);
            $sql = new \DbQuery();
            $sql->select('*')
                ->from(\ModelBrtEvento::$definition['table'])
                ->where('id_evento = ' . (int) $last_event['event_id']);
            $evento = $db->getRow($sql);
            if ($evento) {
                $evento = array_merge($last_event, $evento);
            }
            $evento['carrier_name'] = $carrier['name'];
            $evento['id_carrier'] = $carrier['id_carrier'];
            $evento['link'] = '';
            $evento['image'] = '';
        } else {
            // prelevo le informazioni dalla tabella degli eventi
            $sql = new \DbQuery();
            $sql->select('*')
                ->from(\ModelBrtEvento::$definition['table'])
                ->where('id_evento = ' . (int) $last_event['event_id']);
            $evento = $db->getRow($sql);
            if (!$evento) {
                return false;
            }
            $evento['carrier_name'] = $carrier['name'];
            $evento['id_carrier'] = $carrier['id_carrier'];
            $evento['link'] = '';
            $evento['image'] = '';

            $evento = [
                'id_evento' => 0,
                'id_order' => $id_order,
                'id_carrier' => $carrier['id_carrier'],
                'id_collo' => 0,
                'carrier_name' => $carrier['name'],
                'event_id' => 0,
                'event_name' => '',
                'event_date' => '',
                'rmn' => '',
                'rma' => '',
                'title' => $carrier['name'],
                'link' => '',
                'image' => $context->link->getMediaLink('/img/s/' . $carrier['id_carrier'] . '.jpg'),
                'icon' => '',
                'color' => '#ACACAC',
                'date_shipped' => '',
                'date_delivered' => '',
                'days' => 0,
                'anno_spedizione' => '',
            ];
        }

        return $evento;

        // Se esiste uno stato associato cerco l'evento corrispondente
        if ($id_mpbrtinfo_evento) {
            $sql = new DbQuery();
            $sql->select('*')
                ->from(ModelBrtEvento::$definition['table'])
                ->where('id_order = ' . (int) $id_order)
                ->orderBy('id_mpbrtinfo_evento DESC');
        }

        $icon = $this->displayCarrier->display($id_order);

        return $icon;
    }
}
