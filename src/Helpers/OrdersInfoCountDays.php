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

class OrdersInfoCountDays
{
    /**
     * Conta i giorni lavorativi trascorsi dalla spedizione
     *
     * @param int $id_order
     *
     * @return int
     */
    public static function run($id_order)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('days')
            ->from(\ModelBrtHistory::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy(\ModelBrtHistory::$definition['primary'] . ' DESC');

        return (int) $db->getValue($sql);
    }
}