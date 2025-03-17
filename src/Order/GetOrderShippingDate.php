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

namespace MpSoft\MpBrtInfo\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GetOrderShippingDate
{
    protected $id_order;

    public function __construct($id_order)
    {
        $this->id_order = $id_order;
    }

    protected function run($type = 'date')
    {
        $shippedOrderStates = \ModelBrtEvento::getOrderStatesTypeShipped();
        $date = date('Y-m-d H:i:s');

        if ($shippedOrderStates) {
            $id_state_shipped = implode(',', array_unique(array_column($shippedOrderStates, 'id_order_state')));

            $db = \Db::getInstance();
            $sql = new \DbQuery();

            $sql->select('date_add')
                ->from('order_history')
                ->where('id_order = ' . (int) $this->id_order)
                ->where('id_order_state IN (' . $id_state_shipped . ')')
                ->orderBy('date_add DESC');

            $date = $db->getValue($sql);

            if (!$date) {
                $date = date('Y-m-d H:i:s');
            }
        }

        if ($type == 'year') {
            return (int) date('Y', strtotime($date));
        }

        return $date;
    }

    public function getShippingYear()
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();

        $sql->select('anno_spedizione')
            ->from(\ModelBrtHistory::$definition['table'])
            ->where('id_order=' . (int) $this->id_order)
            ->orderBy(\ModelBrtHistory::$definition['primary'] . ' DESC');
        $year = (int) $db->getValue($sql);

        if ($year) {
            return $year;
        }

        return $this->run('year');
    }

    public function getShippingDate()
    {
        return $this->run('date');
    }
}
