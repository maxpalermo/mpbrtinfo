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
class ModelBrtConfig
{
    const MP_BRT_INFO_VERSION = 'MP_BRT_INFO_VERSION';
    const MP_BRT_INFO_USE_SSL = 'MP_BRT_INFO_USE_SSL';
    const MP_BRT_INFO_CRON_JOB = 'MP_BRT_INFO_CRON_JOB';
    const MP_BRT_INFO_ID_BRT_CUSTOMER = 'MP_BRT_INFO_ID_BRT_CUSTOMER';
    const MP_BRT_INFO_BRT_CARRIERS = 'MP_BRT_INFO_BRT_CARRIERS';
    const MP_BRT_INFO_START_FROM = 'MP_BRT_INFO_START_FROM';
    const MP_BRT_INFO_OS_SKIP = 'MP_BRT_INFO_OS_SKIP';
    const MP_BRT_INFO_OS_SHIPPED = 'MP_BRT_INFO_OS_SHIPPED';
    const MP_BRT_INFO_OS_DELIVERED = 'MP_BRT_INFO_OS_DELIVERED';
    const MP_BRT_INFO_SEARCH_TYPE = 'MP_BRT_INFO_SEARCH_TYPE';
    const MP_BRT_INFO_SEARCH_WHERE = 'MP_BRT_INFO_SEARCH_WHERE';
    const MP_BRT_INFO_SEND_EMAIL = 'MP_BRT_INFO_SEND_EMAIL';
    const MP_BRT_INFO_UPDATE_TRACKING_TABLE = 'MP_BRT_INFO_UPDATE_TRACKING_TABLE';
    const MP_BRT_INFO_ENABLE_AJAX_TABLE = 'MP_BRT_INFO_ENABLE_AJAX_TABLE';

    public static function getRootConfig()
    {
        return 'MP_BRT_INFO_';
    }

    public static function getConfigValue($config_key, $default = null)
    {
        $value = \Configuration::get($config_key);
        if (self::isJson($value)) {
            $value = json_decode($value, true);
        }

        if (!$value && $default !== null) {
            return $default;
        }

        return $value;
    }

    public static function isJson($data)
    {
        if (is_string($data) && json_decode($data) !== null && json_last_error() === JSON_ERROR_NONE) {
            return true;
        }

        return false;
    }

    public static function useSSL()
    {
        return (int) self::getConfigValue(self::MP_BRT_INFO_USE_SSL);
    }

    public static function getEnableAjaxTable()
    {
        return (int) self::getConfigValue(self::MP_BRT_INFO_ENABLE_AJAX_TABLE);
    }

    public static function setEnableAjaxTable($value)
    {
        return self::updateConfigValue(self::MP_BRT_INFO_ENABLE_AJAX_TABLE, $value);
    }

    public static function getIcon($id_event)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('icon')
            ->from('mpbrtinfo_evento')
            ->where('id_evento = ' . (int) $id_event);
        $result = $db->getValue($sql);
        if ($result) {
            return $result;
        }

        return 'priority_high';
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

    public static function getBrtCustomerId()
    {
        return self::getConfigValue(self::MP_BRT_INFO_ID_BRT_CUSTOMER);
    }

    public static function setBrtCustomerId($value)
    {
        return self::updateConfigValue(self::MP_BRT_INFO_ID_BRT_CUSTOMER, $value);
    }

    public static function getBrtCarriersName()
    {
        return self::getConfigValue(self::MP_BRT_INFO_BRT_CARRIERS);
    }

    public static function getBrtCarriersId()
    {
        $carriers = self::getConfigValue(self::MP_BRT_INFO_BRT_CARRIERS);
        if (!$carriers) {
            return [];
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
            $carriers = array_unique(array_column($result, 'id_carrier'));

            return $carriers;
        }

        return false;
    }

    public static function setBrtCarriers($values)
    {
        if (self::isJson($values)) {
            $values = json_decode($values, true);
        }
        if (is_array($values)) {
            $values = array_map(function ($item) {
                return pSQL($item);
            }, $values);

            $values = json_encode($values);
        }

        return self::updateConfigValue(self::MP_BRT_INFO_BRT_CARRIERS, $values);
    }

    public static function getBrtStartFrom()
    {
        return (int) self::getConfigValue(self::MP_BRT_INFO_START_FROM);
    }

    public static function getBrtOsSkip()
    {
        $skip_states = self::getConfigValue(self::MP_BRT_INFO_OS_SKIP);

        return $skip_states;
    }

    public static function setBrtOsSkip($values)
    {
        if (self::isJson($values)) {
            $values = json_decode($values, true);
        }
        if (is_array($values)) {
            $values = array_map(function ($item) {
                return (int) $item;
            }, $values);

            $values = json_encode($values);
        }

        return Configuration::updateValue(self::MP_BRT_INFO_OS_SKIP, $values);
    }

    public static function getBrtOsShipped()
    {
        $shipped_states = self::getConfigValue(self::MP_BRT_INFO_OS_SHIPPED);

        return $shipped_states;
    }

    public static function setBrtOsShipped($values)
    {
        if (is_array($values)) {
            $values = array_map(function ($item) {
                return (int) $item;
            }, $values);

            $values = json_encode($values);
        }

        return Configuration::updateValue(self::MP_BRT_INFO_OS_SHIPPED, $values);
    }

    public static function getBrtOsDelivered()
    {
        $delivered_states = self::getConfigValue(self::MP_BRT_INFO_OS_DELIVERED);

        return $delivered_states;
    }

    public static function setBrtOsDelivered($values)
    {
        if (is_array($values)) {
            $values = array_map(function ($item) {
                return (int) $item;
            }, $values);

            $values = json_encode($values);
        }

        return Configuration::updateValue(self::MP_BRT_INFO_OS_DELIVERED, $values);
    }

    public static function getBrtSearchType()
    {
        return self::getConfigValue(self::MP_BRT_INFO_SEARCH_TYPE);
    }

    public static function getBrtSearchWhere()
    {
        return self::getConfigValue(self::MP_BRT_INFO_SEARCH_WHERE);
    }

    public static function getBrtSendEmail()
    {
        return (int) self::getConfigValue(self::MP_BRT_INFO_SEND_EMAIL);
    }

    public static function getUpdateTrackingTable()
    {
        return (int) self::getConfigValue(self::MP_BRT_INFO_UPDATE_TRACKING_TABLE);
    }

    public static function toArray($value)
    {
        if (!is_array($value) && self::isJson($value)) {
            $value = json_decode($value, true);
        } else {
            $value = [$value];
        }

        return $value;
    }

    public static function getConfigValues()
    {
        $cronTaskInfoShipping = Context::getContext()->link->getModuleLink('mpbrtinfo', 'Cron', ['action' => 'getShippingsInfo']);

        $config = [
            self::MP_BRT_INFO_USE_SSL => (int) self::useSSL(),
            self::MP_BRT_INFO_CRON_JOB => $cronTaskInfoShipping,
            self::MP_BRT_INFO_ID_BRT_CUSTOMER => self::getBrtCustomerId(),
            self::MP_BRT_INFO_BRT_CARRIERS . '[]' => self::getBrtCarriersName(),
            self::MP_BRT_INFO_START_FROM => self::getBrtStartFrom(),
            self::MP_BRT_INFO_OS_SKIP . '[]' => self::getBrtOsSkip(),
            self::MP_BRT_INFO_OS_SHIPPED . '[]' => self::getBrtOsShipped(),
            self::MP_BRT_INFO_OS_DELIVERED . '[]' => self::getBrtOsDelivered(),
            self::MP_BRT_INFO_SEARCH_TYPE => self::getBrtSearchType(),
            self::MP_BRT_INFO_SEARCH_WHERE => self::getBrtSearchWhere(),
            self::MP_BRT_INFO_SEND_EMAIL => self::getBrtSendEmail(),
            self::MP_BRT_INFO_UPDATE_TRACKING_TABLE => (int) self::getUpdateTrackingTable(),
            self::MP_BRT_INFO_ENABLE_AJAX_TABLE => (int) self::getEnableAjaxTable(),
        ];

        return $config;
    }

    public static function setDefaultValues()
    {
        Configuration::updateValue(self::MP_BRT_INFO_USE_SSL, 1);
        Configuration::updateValue(self::MP_BRT_INFO_ID_BRT_CUSTOMER, '');
        Configuration::updateValue(self::MP_BRT_INFO_BRT_CARRIERS, '[]');
        Configuration::updateValue(self::MP_BRT_INFO_START_FROM, 0);
        Configuration::updateValue(self::MP_BRT_INFO_OS_SKIP, '[]');
        Configuration::updateValue(self::MP_BRT_INFO_OS_SHIPPED, '[]');
        Configuration::updateValue(self::MP_BRT_INFO_OS_DELIVERED, '[]');
        Configuration::updateValue(self::MP_BRT_INFO_SEARCH_TYPE, 'RMN');
        Configuration::updateValue(self::MP_BRT_INFO_SEARCH_WHERE, 'ID');
        Configuration::updateValue(self::MP_BRT_INFO_SEND_EMAIL, 0);
        Configuration::updateValue(self::MP_BRT_INFO_UPDATE_TRACKING_TABLE, 0);
        Configuration::updateValue(self::MP_BRT_INFO_ENABLE_AJAX_TABLE, 0);
    }

    public static function updateConfigValue($key, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $result = Configuration::updateValue($key, $value);

        return $result;
    }
}
