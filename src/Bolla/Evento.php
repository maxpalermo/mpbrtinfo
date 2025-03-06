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
    protected $is_delivered;
    protected $is_error;
    protected $is_fermopoint;
    protected $is_refused;
    protected $is_sent;
    protected $is_transit;
    protected $is_waiting;
    protected $row;

    public function __construct($evento)
    {
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

        $this->row = $this->getRow();
    }

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

    protected function getRow()
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('*')
            ->from('mpbrtinfo_evento')
            ->where('id_evento=' . (int) $this->id)
            ->where("name='" . pSQL($this->descrizione) . "'");

        $row = $db->getRow($sql);
        if ($row) {
            $this->is_delivered = $row['is_delivered'];
            $this->is_error = $row['is_error'];
            $this->is_fermopoint = $row['is_fermopoint'];
            $this->is_refused = $row['is_refused'];
            $this->is_sent = $row['is_sent'];
            $this->is_transit = $row['is_transit'];
            $this->is_waiting = $row['is_waiting'];
        }

        return $row;
    }

    public function getOrderStateIdByEventId()
    {
        if ($this->is_delivered) {
            return (int) \Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
        }

        if ($this->is_transit) {
            return (int) \Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT);
        }

        if ($this->is_sent) {
            return (int) \Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);
        }

        if ($this->is_fermopoint) {
            return (int) \Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT);
        }

        if ($this->is_waiting) {
            return (int) \Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING);
        }

        if ($this->is_refused) {
            return (int) \Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED);
        }

        if ($this->is_error) {
            return (int) \Configuration::get(\ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR);
        }

        return null;
    }

    public function getColor($html = false)
    {
        $row = $this->row;
        if (!$row) {
            if ($html) {
                return '#6c757d';
            }

            return 'secondary';
        }
        if ($row['is_fermopoint'] && $row['is_delivered'] ) {
            if ($html) {
                return '#28a745';
            }

            return 'success';
        }
        if ($row['is_fermopoint'] && $row['is_error']) {
            if ($html) {
                return '#dc3545';
            }

            return 'danger';
        }
        if ($row['is_fermopoint'] && $row['is_waiting']) {
            if ($html) {
                return '#ffc107';
            }

            return 'warning';
        }

        if ($row['is_fermopoint'] && $row['is_transit']) {
            if ($html) {
                return '#ffc107';
            }

            return 'warning';
        }

        if ($row['is_delivered']) {
            if ($html) {
                return '#28a745';
            }

            return 'success';
        }
        if ($row['is_fermopoint']) {
            if ($html) {
                return '#ffc107';
            }

            return 'warning';
        }
        if ($row['is_error']) {
            if ($html) {
                return '#dc3545';
            }

            return 'danger';
        }
        if ($row['is_transit']) {
            if ($html) {
                return '#28a745';
            }

            return 'info';
        }
        if ($row['is_waiting']) {
            if ($html) {
                return '#ffc107';
            }

            return 'warning';
        }
        if ($row['is_refused']) {
            if ($html) {
                return '#dc3545';
            }

            return 'danger';
        }
        if ($row['is_sent']) {
            if ($html) {
                return '#17a2b8';
            }

            return 'info';
        }

        return 'secondary';
    }

    public function getIcon()
    {
        $row = $this->row;
        if (!$row) {
            return 'help';
        }
        if ($row['is_delivered'] && $row['is_fermopoint']) {
            return 'check_circle';
        }
        if ($row['is_fermopoint'] && $row['is_error']) {
            return 'report';
        }
        if ($row['is_fermopoint'] && $row['is_waiting']) {
            return 'warning';
        }
        if ($row['is_error'] && $row['is_refused']) {
            return 'block';
        }
        if ($row['is_delivered']) {
            return 'check_circle';
        }
        if ($row['is_fermopoint']) {
            return 'warning';
        }
        if ($row['is_error']) {
            return 'priority_high';
        }
        if ($row['is_transit']) {
            return 'speed';
        }
        if ($row['is_waiting']) {
            return 'pending';
        }
        if ($row['is_refused']) {
            return 'block';
        }
        if ($row['is_sent']) {
            return 'local_shipping';
        }

        return 'secondary';
    }

    public function isDelivered()
    {
        return $this->is_delivered;
    }

    public function isError()
    {
        return $this->is_error;
    }

    public function isFermopoint()
    {
        return $this->is_fermopoint;
    }

    public function isRefused()
    {
        return $this->is_refused;
    }

    public function isSent()
    {
        return $this->is_sent;
    }

    public function isTransit()
    {
        return $this->is_transit;
    }

    public function isWaiting()
    {
        return $this->is_waiting;
    }

    public function getRowData()
    {
        return $this->row;
    }

    public function getLabel()
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('*')
            ->from('mpbrtinfo_evento')
            ->where('id_evento = ' . (int) $this->id);

        $result = $db->getRow($sql);

        if (!$result) {
            return 'N/A';
        }

        if ($result['is_fermopoint'] && $result['is_delivered']) {
            return 'Ritirato Fermopoint';
        }

        if ($result['is_fermopoint'] && $result['is_waiting']) {
            return 'Attesa Fermopoint';
        }

        if ($result['is_fermopoint'] && $result['is_transit']) {
            return 'Arrivato Fermopoint';
        }

        if ($result['is_fermopoint'] && $result['is_refused']) {
            return 'Non Prelevato';
        }

        if ($result['is_error'] && $result['is_refused']) {
            return 'Rifiutato';
        }

        if ($result['is_error']) {
            return 'Errore';
        }

        if ($result['is_transit']) {
            return 'Transito';
        }

        if ($result['is_sent']) {
            return 'Spedito';
        }

        if ($result['is_waiting']) {
            return 'Attesa';
        }

        if ($result['is_refused']) {
            return 'Rifiutato';
        }

        if ($result['is_delivered']) {
            return 'Consegnato';
        }

        return 'N/A';
    }
}
