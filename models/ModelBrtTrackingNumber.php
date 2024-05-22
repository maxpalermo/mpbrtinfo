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

class ModelBrtTrackingNumber extends ObjectModel
{
    const SENT = 'SENT';
    const DELIVERED = 'DELIVERED';
    const TRANSIT = 'TRANSIT';
    const FERMOPOINT = 'FERMOPOINT';
    const REFUSED = 'REFUSED';
    const WAITING = 'WAITING';
    const ERROR = 'ERROR';

    protected static $errors = [];
    public $id_order;
    public $id_order_state;
    public $id_brt_state;
    public $id_collo;
    public $rmn;
    public $tracking_number;
    public $current_state;
    public $anno_spedizione;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mpbrtinfo_tracking_number',
        'primary' => 'id_mpbrtinfo_tracking_number',
        'multilang' => false,
        'fields' => [
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_order_state' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_brt_state' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 16,
                'required' => false,
            ],
            'id_collo' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 128,
                'required' => false,
            ],
            'rmn' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'tracking_number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 128,
                'required' => false,
            ],
            'current_state' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 32,
                'required' => false,
            ],
            'anno_spedizione' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
        ],
    ];

    public static function getOrderHistory($id_order)
    {
        $sql = new DbQuery();
        $sql->select('o.id_order, o.id_order_state, o.date_add, o.date_upd, oh.id_brt_state, oh.tracking_number, oh.id_collo, oh.anno_spedizione');
        $sql->from('orders', 'o');
        $sql->leftJoin('mp_brtinfo_tracking_number', 'oh', 'o.id_order = oh.id_order');
        $sql->where('o.id_order_state = oh.id_order_state');
        $sql->where('o.id_order = ' . (int) $id_order);

        return Db::getInstance()->executeS($sql);
    }

    public static function addHistory($id_order, $id_order_state, $id_brt_state, $tracking_number, $id_collo, $anno_spedizione)
    {
        $history = new ModelBrtTrackingNUmber();
        $history->id_order = (int) $id_order;
        $history->id_order_state = (int) $id_order_state;
        $history->id_brt_state = (int) $id_brt_state;
        $history->tracking_number = pSQL($tracking_number);
        $history->id_collo = (int) $id_collo;
        $history->anno_spedizione = (int) $anno_spedizione;
        $history->date_add = date('Y-m-d H:i:s');
        $history->date_upd = date('Y-m-d H:i:s');

        return $history->add();
    }

    public static function getOrderByLastOrderState(int $id_order_state)
    {
        $sql = new DbQuery();
        $sql->select('id_order')
            ->from(self::$definition['table'])
            ->where('id_order_state = ' . (int) $id_order_state)
            ->orderBy('date_add DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function getOrdersByLastCurrentState(string $order_state)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();

        $sql->select('a.id_order, a.id_order_state, a.tracking_number, a.current_state, a.date_add, o.total_paid_tax_incl, c.email, concat(c.firstname, " ", c.lastname) as customer, os.name as order_state')
            ->from(self::$definition['table'], 'a')
            ->leftJoin('orders', 'o', 'a.id_order = o.id_order and o.id_order is not null')
            ->leftJoin('customer', 'c', 'o.id_customer = c.id_customer and c.id_customer is not null')
            ->leftJoin('order_state_lang', 'os', 'a.id_order_state = os.id_order_state and os.id_lang = ' . $id_lang . ' and os.id_order_state is not null')
            ->groupBy('a.id_order')
            ->having("a.current_state = '" . pSQL($order_state) . "'")
            ->having('a.date_add = MAX(a.date_add)')
            ->orderBy('a.date_add DESC')
            ->limit(50);
        $sql = $sql->build();
        $rows = Db::getInstance()->executeS($sql);

        if (!$rows) {
            return [];
        }

        return $rows;
    }

    public static function getTrackingNumberByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->select('tracking_number')
            ->from(self::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy('date_add DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function getTrackingNumberByIdCollo($id_collo, $anno)
    {
        $sql = new DbQuery();
        $sql->select('tracking_number')
            ->from(self::$definition['table'])
            ->where('id_collo = ' . (int) $id_collo)
            ->where('anno_spedizione = ' . (int) $anno)
            ->orderBy('date_add DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function getIdColloByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->select('id_collo')
            ->from(self::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->where('id_collo is not null')
            ->orderBy('date_add DESC');

        $value = Db::getInstance()->getValue($sql);
        if ($value) {
            return $value;
        }

        return '';
    }

    public static function getIdColloByTrackingNumber($tracking_number, $anno)
    {
        $sql = new DbQuery();
        $sql->select('id_collo')
            ->from(self::$definition['table'])
            ->where('tracking_number = "' . pSQL($tracking_number) . '"')
            ->where('anno_spedizione = ' . (int) $anno)
            ->orderBy('date_add DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function setAsSent($id_order, $tracking)
    {
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $id_order_state = (int) $order->current_state;
        $id_order_state_sent = Configuration::get('MPBRTINFO_ORDER_STATE_SENT');
        $current_state = $tracking ? 'TRANSIT' : 'SENT';

        $model = new ModelBrtTrackingNumber();
        $model->id_order = (int) $id_order;
        $model->id_order_state = $id_order_state;
        $model->id_brt_state = self::getBrtIdState(ModelBrtEvento::EVENT_SENT);
        $model->id_collo = '';
        $model->rmn = $id_order;
        $model->tracking_number = $tracking;
        $model->current_state = $current_state;
        $model->anno_spedizione = date('Y');
        $model->date_add = date('Y-m-d H:i:s');

        $res = $model->add();
        if ($res) {
            $order = new Order($id_order);
            $order->setCurrentState($id_order_state_sent);
        }

        return $res;
    }

    public static function getBrtIdState($state = '')
    {
        $state = ModelBrtEvento::getEventi($state);
        if ($state) {
            foreach ($state as $row) {
                return $row['id_evento'];
            }
        }

        return 0;
    }
}