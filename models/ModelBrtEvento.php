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
class ModelBrtEvento extends ObjectModel
{
    const EVENT_TRANSIT = 'is_transit=1';
    const EVENT_DELIVERED = 'is_delivered=1';
    const EVENT_ERROR = 'is_error=1';
    const EVENT_FERMOPOINT = 'is_fermopoint=1 and is_delivered=0';
    const EVENT_REFUSED = 'is_refused=1';
    const EVENT_WAITING = 'is_waiting=1 and is_fermopoint=0';
    const EVENT_SENT = 'is_sent=1';

    public $id_evento;
    public $name;
    public $is_error;
    public $is_delivered;
    public $is_transit;
    public $is_fermopoint;
    public $is_waiting;
    public $is_refused;
    public $is_sent;
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
            'is_error' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_transit' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_delivered' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_fermopoint' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_waiting' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_refused' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_sent' => [
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

    public function isError()
    {
        return (int) $this->is_error;
    }

    public function isRefused()
    {
        return (int) $this->is_refused;
    }

    public function isTransit()
    {
        return (int) $this->is_transit;
    }

    public function isDelivered()
    {
        return (int) $this->is_delivered;
    }

    public function isFermopoint()
    {
        return (int) $this->is_fermopoint;
    }

    public function isWaiting()
    {
        return (int) $this->is_waiting;
    }

    public function isSent()
    {
        return (int) $this->is_sent;
    }

    public static function getEventi($type = '')
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->orderBy('name');
        switch ($type) {
            case self::EVENT_DELIVERED:
            case self::EVENT_ERROR:
            case self::EVENT_FERMOPOINT:
            case self::EVENT_TRANSIT:
            case self::EVENT_WAITING:
            case self::EVENT_SENT:
                $sql->where($type);

                break;
        }
        $rows = $db->executeS($sql);

        return $rows;
    }

    public static function getOrderStatesByBrtState($brtState)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where($brtState)
            ->orderBy('name');
        $rows = $db->executeS($sql);

        if ($rows) {
            $id_states = array_column($rows, 'id_evento');
            $id_states = array_map(function ($item) {
                return "'" . pSQL($item) . "'";
            }, $id_states);

            return $id_states;
        }

        return [];
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

    public function setFlag($flag, $value)
    {
        $flags = [
            'is_error',
            'is_delivered',
            'is_transit',
            'is_fermopoint',
            'is_refused',
            'is_waiting',
            'is_sent',
        ];
        if (!in_array($flag, $flags)) {
            throw new Exception('Flag unknown', 1003);
        }
        $object = new ModelBrtEvento($this->id);
        if (!Validate::isLoadedObject($object)) {
            throw new Exception('Event ' . $this->name . ' not exists.', 1002);
        }

        return Db::getInstance()->update(
            self::$definition['table'],
            [
                $flag . '=' . (int) $value,
            ],
            self::$definition['primary'] . '=' . (int) $this->id
        );
    }
}
