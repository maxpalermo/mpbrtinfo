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

class ModelBrtDelivered extends ObjectModel
{
    public $conn;

    public $id_order;
    public $date_shipped;
    public $date_delivered;
    public $tracking_number;
    public $days;

    protected $module_name;
    protected $module;

    public static $definition = [
        'table' => 'mp_brtinfo_delivered',
        'primary' => 'id_order',
        'multilang' => false,
        'fields' => [
            'date_shipped' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
                'required' => true,
            ],
            'date_delivered' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'tracking_number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'size' => 64,
            ],
            'days' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
        ],
    ];
}
