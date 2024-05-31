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
class ModelBrtConfig extends ConfigurationCore
{
    const MP_BRT_INFO_CRON_JOB = 'MP_BRT_INFO_CRON_JOB';
    const MP_BRT_INFO_ID_BRT_CUSTOMER = 'MP_BRT_INFO_ID_BRT_CUSTOMER';
    const MP_BRT_INFO_BRT_CARRIERS = 'MP_BRT_INFO_BRT_CARRIERS';
    const MP_BRT_INFO_USE_SSL = 'MP_BRT_INFO_USE_SSL';
    const MP_BRT_INFO_OS_CHECK_FOR_TRACKING = 'MP_BRT_INFO_OS_CHECK_FOR_TRACKING';
    const MP_BRT_INFO_OS_CHECK_FOR_DELIVERED = 'MP_BRT_INFO_OS_CHECK_FOR_DELIVERED';
    const MP_BRT_INFO_SEARCH_TYPE = 'MP_BRT_INFO_SEARCH_TYPE';
    const MP_BRT_INFO_SEARCH_WHERE = 'MP_BRT_INFO_SEARCH_WHERE';
    const MP_BRT_INFO_SEARCH_WHERE_ID = 'MP_BRT_INFO_SEARCH_WHERE_ID';
    const MP_BRT_INFO_SEARCH_WHERE_REF = 'MP_BRT_INFO_SEARCH_WHERE_REF';
    const MP_BRT_INFO_EVENT_TRANSIT = 'MP_BRT_INFO_EVENT_TRANSIT';
    const MP_BRT_INFO_EVENT_DELIVERED = 'MP_BRT_INFO_EVENT_DELIVERED';
    const MP_BRT_INFO_EVENT_ERROR = 'MP_BRT_INFO_EVENT_ERROR';
    const MP_BRT_INFO_EVENT_FERMOPOINT = 'MP_BRT_INFO_EVENT_FERMOPOINT';
    const MP_BRT_INFO_EVENT_REFUSED = 'MP_BRT_INFO_EVENT_REFUSED';
    const MP_BRT_INFO_EVENT_WAITING = 'MP_BRT_INFO_EVENT_WAITING';
    const MP_BRT_INFO_EVENT_SENT = 'MP_BRT_INFO_EVENT_SENT';
    const MP_BRT_INFO_EVENT_UNKNOWN = 'MP_BRT_INFO_EVENT_UNKNOWN';

    public static function getRootConfig()
    {
        return 'MP_BRT_INFO_';
    }

    public static function getCheckEvents()
    {
        $transit = self::getConfigValue(self::MP_BRT_INFO_EVENT_TRANSIT);
        $error = self::getConfigValue(self::MP_BRT_INFO_EVENT_ERROR);
        $fermopoint = self::getConfigValue(self::MP_BRT_INFO_EVENT_FERMOPOINT);
        $refused = self::getConfigValue(self::MP_BRT_INFO_EVENT_REFUSED);
        $waiting = self::getConfigValue(self::MP_BRT_INFO_EVENT_WAITING);
        $sent = self::getConfigValue(self::MP_BRT_INFO_EVENT_SENT);
        $events = array_merge($transit, $error, $fermopoint, $refused, $waiting, $sent);

        return $events;
    }

    public static function getCarriers()
    {
        $carriers = self::get('MP_BRT_INFO_BRT_CARRIERS');
        if (!$carriers) {
            return [];
        }

        if (!is_array($carriers)) {
            $carriers = json_decode($carriers, true);
        }

        $carriers = array_map(function ($item) {
            return "'" . pSQL($item) . "'";
        }, $carriers);

        $carriers = implode(',', $carriers);

        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_carrier')
            ->from('carrier')
            ->where('name IN (' . $carriers . ')');
        $result = $db->executeS($sql);

        if ($result) {
            $carriers = array_column($result, 'id_carrier');

            return $carriers;
        }

        return false;
    }

    public static function setDefaultValues()
    {
        $values = [
            self::MP_BRT_INFO_BRT_CARRIERS => [],
            self::MP_BRT_INFO_OS_CHECK_FOR_TRACKING => [],
            self::MP_BRT_INFO_OS_CHECK_FOR_DELIVERED => [],
            self::MP_BRT_INFO_ID_BRT_CUSTOMER => '',
            self::MP_BRT_INFO_USE_SSL => false,
            self::MP_BRT_INFO_SEARCH_TYPE => 'RMN',
            self::MP_BRT_INFO_SEARCH_WHERE => self::MP_BRT_INFO_SEARCH_WHERE_ID,
            self::MP_BRT_INFO_EVENT_DELIVERED => [],
            self::MP_BRT_INFO_EVENT_ERROR => [],
            self::MP_BRT_INFO_EVENT_FERMOPOINT => [],
            self::MP_BRT_INFO_EVENT_REFUSED => [],
            self::MP_BRT_INFO_EVENT_TRANSIT => [],
            self::MP_BRT_INFO_EVENT_WAITING => [],
            self::MP_BRT_INFO_EVENT_SENT => [],
            self::MP_BRT_INFO_EVENT_UNKNOWN => [],
        ];

        foreach ($values as $key => $value) {
            \Configuration::updateValue($key, $value);
        }
    }

    public static function getConfigValue($config_key)
    {
        $value = \Configuration::get($config_key);
        $json = json_decode($value, true);
        if (is_array($json)) {
            $value = $json;
        }

        return $value;
    }

    public static function isJson($data)
    {
        if (!empty($data) && is_string($data) && is_array($json = json_decode($data, true))) {
            return $json;
        }

        return false;
    }

    public static function getIcon($event)
    {
        $path = Module::getInstanceByName('mpbrtinfo')->getPathUri() . 'views/img/icons/';

        switch ($event) {
            case self::MP_BRT_INFO_EVENT_DELIVERED:
            case self::MP_BRT_INFO_EVENT_ERROR:
            case self::MP_BRT_INFO_EVENT_FERMOPOINT:
            case self::MP_BRT_INFO_EVENT_REFUSED:
            case self::MP_BRT_INFO_EVENT_TRANSIT:
            case self::MP_BRT_INFO_EVENT_WAITING:
            case self::MP_BRT_INFO_EVENT_SENT:
                $path .= $event . '.png';

                break;
            case self::MP_BRT_INFO_EVENT_UNKNOWN:
            default:
                $path .= 'icon-unknown.png';
        }

        return $path;
    }

    public static function useSSL()
    {
        return (int) self::getConfigValue(self::MP_BRT_INFO_USE_SSL);
    }

    public static function getHistoryStateYear($id_order, $tracking_state = null, &$row = null)
    {
        if (!$tracking_state) {
            $tracking_state = self::getConfigValue(self::MP_BRT_INFO_OS_CHECK_FOR_TRACKING);
        }
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*, YEAR(date_add) as `os_year`')
            ->from('order_history')
            ->where('id_order=' . (int) $id_order)
            ->where('id_order_state in (' . implode(',', $tracking_state) . ')')
            ->orderBy('date_add DESC');

        $row = $db->getRow($sql);
        if ($row) {
            $year = $row['os_year'];
        } else {
            $order = new Order($id_order);
            $date_add = strtotime($order->date_add);
            $year = date('Y', $date_add);
        }

        return $year;
    }

    public static function getEsiti()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_esito,testo1,testo2')
            ->from('mpbrtinfo_esito')
            ->orderBy('id_esito ASC');
        $result = $db->executeS($sql);
        $esiti = [];
        foreach ($result as $row) {
            $esiti[$row['id_esito']] = $row['testo1'] . ' ' . $row['testo2'];
        }

        return $esiti;
    }
}
