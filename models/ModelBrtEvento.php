<?php

use MpSoft\MpBrtInfo\Helpers\ConvertIdColloToTracking;
use MpSoft\MpBrtInfo\Helpers\GetTrackingFromOrderCarrier;
use MpSoft\MpBrtInfo\JSON\JsonDecoder;

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
class ModelBrtEvento extends ObjectModel
{
    public $id_evento;
    public $name;
    public $id_order_state;
    public $email;
    public $icon;
    public $color;
    public $is_shipped;
    public $is_delivered;
    public $date_add;
    public $date_upd;
    protected static $model_name = 'ModelBrtEvento';

    public static $definition = [
        'table' => 'mpbrtinfo_evento',
        'primary' => 'id_mpbrtinfo_evento',
        'multilang' => false,
        'fields' => [
            'id_evento' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
            ],
            'id_order_state' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false,
            ],
            'email' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => false,
            ],
            'icon' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => false,
            ],
            'color' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => false,
            ],
            'is_shipped' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_delivered' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ],
    ];

    public static function getOS()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $db = db::getInstance();
        $sql = new DbQuery();
        $sql->select('os.*')
            ->select('osl.name')
            ->from('order_state', 'os')
            ->leftJoin('order_state_lang', 'osl', 'osl.id_order_state = os.id_order_state AND osl.id_lang = ' . (int) $id_lang)
            ->orderBy('osl.name');

        try {
            if ($os = $db->executeS($sql)) {
                $out = [];
                foreach ($os as &$o) {
                    $out[$o['id_order_state']] = $o;
                }

                return $out;
            }
        } catch (\Throwable $th) {
            Context::getContext()->controller->errors[] = $th->getMessage();
            Context::getContext()->controller->errors[] = $sql;

            return [];
        }
    }

    public static function getList()
    {
        $os = self::getOS();
        $table = self::$definition['table'];
        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        $sql = new DbQuery();
        /*
        $sql->select('ev.*')
            ->select('COALESCE(osl.name, "Non cambiare stato") as order_state_name')
            ->select('os.color as order_state_color')
            ->from($table, 'ev')
            ->leftJoin('order_state', 'os', 'os.id_order_state = ev.id_order_state')
            ->leftJoin('order_state_lang', 'osl', 'osl.id_order_state = os.id_order_state AND osl.id_lang = ' . (int) $id_lang)
            ->orderBy('ev.name');
        */

        $sql = new DbQuery();
        $sql->select('*')
            ->from($table)
            ->orderBy('name');

        try {
            $rows = $db->executeS($sql);
        } catch (\Throwable $th) {
            Context::getContext()->controller->errors[] = $th->getMessage();
            Context::getContext()->controller->errors[] = $sql;

            return [];
        }

        if (!$rows) {
            return [];
        }

        foreach ($rows as &$row) {
            if (isset($os[$row['id_order_state']])) {
                $row['order_state_name'] = $os[$row['id_order_state']]['name'];
                $row['order_state_color'] = $os[$row['id_order_state']]['color'];
            } else {
                $row['order_state_name'] = 'Non cambiare stato';
                $row['order_state_color'] = '#505050';
            }
        }

        return $rows;
    }

    /**
     * Restituisce un oggetto Evento dal codice Evento Bartolini
     *
     * @param string $id Id evento Bartolini
     *
     * @return bool|ModelBrtEvento
     */
    public static function getEvento($id)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where('id_evento = ' . (int) $id);
        $id_evt = (int) $db->getValue($sql);
        if ($id_evt) {
            return new ModelBrtEvento($id_evt);
        }

        return false;
    }

    public static function getEventi($groupBy = '')
    {
        $groupByList = [
            'icon',
            'color',
            'email',
            'id_order_state',
        ];
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->orderBy('name');

        $rows = $db->executeS($sql);
        if ($groupBy && in_array($groupBy, $groupByList)) {
            $groups = [];
            foreach ($rows as $row) {
                $groups[$row[$groupBy]][] = new ModelBrtEvento($row[self::$definition['primary']]);
            }

            return $groups;
        }

        $eventi = [];
        foreach ($rows as $row) {
            $eventi[] = new ModelBrtEvento($row[self::$definition['primary']]);
        }

        return $eventi;
    }

    public static function getIdByEventName($eventName)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where('name = \'' . pSQL($eventName) . '\'');
        $id = $db->getValue($sql);

        if (!$id) {
            return false;
        }

        return $id;
    }

    public static function getById($id)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where('id_evento = \'' . pSQL($id) . '\'');
        $id_row = (int) $db->getValue($sql);
        if ($id_row) {
            return new ModelBrtEvento($id_row);
        }

        return false;
    }

    public function add($auto_date = true, $null_values = false)
    {
        $exists = self::getById($this->id);
        if (!$exists) {
            return parent::add($auto_date, $null_values);
        }

        throw new Exception('Event already exists with id ' . $exists->id, 1001);
    }

    public static function getEmail($event_id)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('email')
            ->from(self::$definition['table'])
            ->where("id_evento = '{$event_id}'");
        $email = $db->getValue($sql);

        return $email;
    }

    public static function getEventFull($id_order, $id_event = null)
    {
        $evento = [];
        $order = self::getOrder($id_order);
        if (!$order) {
            return false;
        }
        $carrier = self::getCarrier($order['id_carrier']);
        if (!$carrier) {
            return false;
        }
        if (!$id_event) {
            $evt = self::getLastEventHistory($id_order);
            if ($evt) {
                $id_event = $evt['event_id'];
            } else {
                return false;
            }
        }
        $event = self::getEvent($id_event);
        if (!$event) {
            return false;
        }
        $event_note = [];
        $lastEventHistory = self::getLastEventHistory($id_order, $id_event);
        if ($lastEventHistory) {
            // Se ancora non c'è un id_collo, cerco nella tabella order_carrier
            if (!$lastEventHistory['id_collo']) {
                $id_collo = GetTrackingFromOrderCarrier::get($carrier['id_carrier'], $id_order);
                $lastEventHistory['id_collo'] = ConvertIdColloToTracking::convert($id_collo);
            }
            if ($lastEventHistory['note']) {
                $event_note = JsonDecoder::decodeJson($lastEventHistory['note'], []);
            }
        }

        $evento = [
            'event_id' => $event['id_evento'],
            'event_name' => $event['name'],
            'event_id_order_state' => $event['id_order_state'],
            'event_email' => $event['email'],
            'event_icon' => $event['icon'],
            'event_color' => $event['color'],
            'event_is_shipped' => $event['is_shipped'],
            'event_is_delivered' => $event['is_delivered'],
            'order_id' => $order['id_order'],
            'carrier_id' => $carrier['id_carrier'],
            'carrier_name' => $carrier['name'],
            'event_filiale_id' => $lastEventHistory['event_filiale_id'] ?? '',
            'event_filiale_name' => $lastEventHistory['event_filiale_name'] ?? '',
            'id_collo' => $lastEventHistory['id_collo'] ?? '',
            'rmn' => $lastEventHistory['rmn'] ?? '',
            'rma' => $lastEventHistory['rma'] ?? '',
            'anno_spedizione' => $lastEventHistory['anno_spedizione'] ?? '',
            'date_shipped' => $lastEventHistory['date_shipped'] ?? '',
            'date_delivered' => $lastEventHistory['date_delivered'] ?? '',
            'days' => $lastEventHistory['days'] ?? '',
            'is_shipped' => $event['is_shipped'],
            'is_delivered' => $event['is_delivered'],
            'note' => $event_note,
        ];

        return $evento;
    }

    public static function getOrder($id_order)
    {
        $order = new Order($id_order, Context::getContext()->language->id);
        if (Validate::isLoadedObject($order)) {
            return [
                'id_order' => $order->id,
                'current_state' => $order->current_state,
                'id_carrier' => $order->id_carrier,
                'total_paid_tax_incl' => $order->total_paid_tax_incl,
                'date_add' => $order->date_add,
            ];
        }

        return false;
    }

    public static function getCarrier($id_carrier)
    {
        $carrier = new Carrier($id_carrier, Context::getContext()->language->id);
        if (Validate::isLoadedObject($carrier)) {
            return [
                'id_carrier' => $carrier->id,
                'name' => $carrier->name,
            ];
        }

        return false;
    }

    public static function getEvent($id_event)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where('id_evento = \'' . PSQL($id_event) . '\'');
        $row = $db->getRow($sql);

        if ($row) {
            return [
                'id_evento' => $row['id_evento'],
                'name' => $row['name'],
                'id_order_state' => $row['id_order_state'],
                'email' => $row['email'],
                'icon' => $row['icon'],
                'color' => $row['color'],
                'is_shipped' => $row['is_shipped'],
                'is_delivered' => $row['is_delivered'],
            ];
        }

        return false;
    }

    public static function getLastEventHistory($id_order, $id_event = null)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(ModelBrtHistory::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy(ModelBrtHistory::$definition['primary'] . ' DESC');
        if ($id_event) {
            $sql->where('event_id = ' . (int) $id_event);
        }
        $row = $db->getRow($sql);

        if (!$row) {
            return false;
        }

        return $row;
    }

    public static function getOrderStatesTypeShipped()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('a.id_order_state, b.name')
            ->from(ModelBrtEvento::$definition['table'], 'a')
            ->leftJoin('order_state_lang', 'b', 'a.id_order_state = b.id_order_state AND b.id_lang = ' . (int) Context::getContext()->language->id)
            ->where('a.is_shipped = 1');
        $rows = $db->executeS($sql);

        return $rows;
    }

    public static function getOrderStatesTypeDelivered()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('a.id_order_state, b.name')
            ->from(ModelBrtEvento::$definition['table'], 'a')
            ->leftJoin('order_state_lang', 'b', 'a.id_order_state = b.id_order_state AND b.id_lang = ' . (int) Context::getContext()->language->id)
            ->where('a.is_delivered = 1')
            ->orderBy('b.name ASC');
        $rows = $db->executeS($sql);

        return $rows;
    }

    public static function getEventsByIdOrderState($id_order_state)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where('id_order_state = ' . (int) $id_order_state);
        $rows = $db->executeS($sql);

        return $rows;
    }

    public function isShipped()
    {
        return (bool) $this->is_shipped;
    }

    public function isDelivered()
    {
        return (bool) $this->is_delivered;
    }

    public static function getIdOrderStateByIdEvent($id_event)
    {
        $event = self::getEvento($id_event);
        if (Validate::isLoadedObject($event)) {
            return (int) $event->id_order_state;
        }

        return false;
    }
}
