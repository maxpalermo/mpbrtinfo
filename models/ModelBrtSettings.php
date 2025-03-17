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
class ModelBrtSettings
{
    private static $instance;
    protected $use_ssl;
    protected $id_brt_customer;
    protected $carriers;
    protected $start_from_order_id;
    protected $os_skip;
    protected $search_type;
    protected $search_where;
    protected $send_email;
    protected $config = [];
    protected $search_type_constrains = ['RMN', 'RMA', 'ID_COLLO'];
    protected $search_where_constrains = ['ID_ORDER', 'REFERENCE'];
    protected $errors = [];

    private function __construct()
    {
        $this->config = Configuration::get('MPBRTINFO_SETTINGS');
        if ($this->config) {
            $this->config = json_decode($this->config, true);
        }
        $this->use_ssl = $this->config['use_ssl'] ?? 0;
        $this->getSettings();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConfigKeys()
    {
        $configKeys = [
            'id_brt_customer', 'carriers', 'start_from_order_id', 'os_skip',
            'search_type', 'search_where', 'send_email',
        ];

        return $configKeys;
    }

    public function getConfigValues()
    {
        $this->getSettings();

        return $this->config;
    }

    public function getSettings()
    {
        $configKeys = $this->getConfigKeys();
        $values = json_decode(Configuration::get('MPBRTINFO_SETTINGS'), true);
        $config = [];
        foreach ($configKeys as $key) {
            $this->{$key} = ($values[$key] ?? '');
            $config[$key] = $this->getSetting($key);
        }
        $this->config = $config;

        return $config;
    }

    public function getSetting($key)
    {
        $key = 'get' . Tools::ucFirst(Tools::toCamelCase($key));

        return $this->{$key}();
    }

    public function getUseSsl()
    {
        return (int) $this->use_ssl;
    }

    public function getIdBrtCustomer()
    {
        return (int) $this->id_brt_customer;
    }

    public function getCarriers()
    {
        if (!is_array($this->carriers)) {
            $this->carriers = json_decode($this->carriers, true);
        }

        if (!$this->carriers) {
            return [];
        }

        return $this->carriers;
    }

    public function getStartFromOrderId()
    {
        return (int) $this->start_from_order_id;
    }

    public function getOsSkip()
    {
        if (!is_array($this->os_skip)) {
            $this->os_skip = json_decode($this->os_skip, true);
        }

        if (!$this->os_skip) {
            return [];
        }

        return $this->os_skip;
    }

    public function getSearchType()
    {
        if (!in_array($this->search_type, $this->search_type_constrains)) {
            return $this->search_type_constrains[0];
        }

        return $this->search_type;
    }

    public function getSearchWhere()
    {
        if (!in_array($this->search_where, $this->search_where_constrains)) {
            return $this->search_where_constrains[0];
        }

        return $this->search_where;
    }

    public function getSendEmail()
    {
        return (int) $this->send_email;
    }

    public function updateSettings($config)
    {
        $this->config = $config;
        $json = json_encode($config);
        Configuration::updateValue('MPBRTINFO_SETTINGS', $json);
    }

    public function updateSetting($key, $value)
    {
        if ($key == 'search_type' && !in_array($value, $this->search_type_constrains)) {
            $this->errors[] = 'Invalid search type';

            return false;
        }
        if ($key == 'search_where' && !in_array($value, $this->search_where_constrains)) {
            $this->errors[] = 'Invalid search where';

            return false;
        }

        $this->getSettings();
        $this->config[$key] = $value;

        return $this->updateSettings($this->config);
    }

    public function clearSettings()
    {
        Configuration::updateValue('MPBRTINFO_SETTINGS', null);
        $this->getSettings();
    }

    public function clearSetting($key)
    {
        $this->updateSetting($key, null);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
