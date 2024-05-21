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
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
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
                'validate' => 'isString',
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
        $sql = new DbQuery();
        $sql->select('id_order, current_state, date_add')
            ->from(self::$definition['table'])
            ->groupBy('id_order')
            ->having("current_state = '" . pSQL($order_state) . "'")
            ->having('date_add = MAX(date_add)')
            ->orderBy('date_add DESC');

        $orders = Db::getInstance()->executeS($sql);
        if ($orders) {
            $id_orders = array_column($orders, 'id_order');
            $id_lang = (int) Context::getContext()->language->id;
            $sql = new DbQuery();
            $sql->select('a.id_order, a.total_paid_tax_incl, c.email, concat(c.firstname, " ", c.lastname) as customer, a.date_add, a.current_state, os.name as order_state')
                ->from('orders', 'a')
                ->leftJoin('customer', 'c', 'a.id_customer = c.id_customer and c.id_customer is not null')
                ->leftJoin('order_state_lang', 'os', 'a.current_state = os.id_order_state and os.id_lang = ' . $id_lang . ' and os.id_order_state is not null')
                ->where('a.id_order in (' . implode(',', $id_orders) . ')')
                ->orderBy('a.id_order DESC')
                ->limit(50);
            $orderList = Db::getInstance()->executeS($sql);
            if ($orderList) {
                foreach ($orderList as &$order) {
                    foreach ($orders as $key => $brt_order) {
                        if ($order['id_order'] == $brt_order['id_order']) {
                            $order['date_add'] = $brt_order['date_add'];
                            unset($orders[$key]);

                            break;
                        }
                    }
                }

                return $orderList;
            }
        }

        return [];
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
        $id_order_state_sent = (int) Configuration::get('MPBRTINFO_ORDER_STATE_SENT');
        $model = new ModelBrtTrackingNumber();
        $model->id_order = (int) $id_order;
        $model->id_order_state = $id_order_state_sent;
        $model->id_brt_state = self::getBrtIdState(ModelBrtEvento::EVENT_SENT);
        $model->id_collo = '';
        $model->rmn = $id_order;
        $model->tracking_number = $tracking;
        $model->current_state = 'SENT';
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